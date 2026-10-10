<?php

namespace App\Support\Payments;

use App\Models\Payment;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/** PayPal Checkout via the Orders v2 API. Docs: https://developer.paypal.com/docs/api/orders/v2/ */
class PayPalGateway extends Gateway
{
    /** Currencies PayPal can charge in. https://developer.paypal.com/api/rest/reference/currency-codes/ */
    private const CURRENCIES = ['AUD', 'BRL', 'CAD', 'CNY', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'THB', 'USD'];

    /** PayPal does not accept decimals for these. */
    private const NO_DECIMALS = ['HUF', 'JPY', 'TWD'];

    public function key(): string
    {
        return 'paypal';
    }

    public function name(): string
    {
        return 'PayPal';
    }

    public function description(): string
    {
        return 'PayPal balance, cards and Pay Later';
    }

    public function fields(): array
    {
        return [
            'mode' => ['label' => 'Mode', 'type' => 'select', 'options' => ['sandbox' => 'Sandbox (testing)', 'live' => 'Live']],
            'client_id' => ['label' => 'Client ID', 'type' => 'text', 'required' => true],
            'client_secret' => ['label' => 'Secret', 'type' => 'secret', 'required' => true],
            'webhook_id' => ['label' => 'Webhook ID (optional)', 'type' => 'text', 'help' => 'Payments are confirmed when the guest returns. Add the webhook URL below in PayPal (event PAYMENT.CAPTURE.COMPLETED) for extra reliability.'],
        ];
    }

    public function supportedCurrencies(): ?array
    {
        return self::CURRENCIES;
    }

    private function baseUrl(): string
    {
        return $this->sandbox() ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
    }

    private function client(): PendingRequest
    {
        $token = Http::baseUrl($this->baseUrl())
            ->withBasicAuth((string) $this->config('client_id'), (string) $this->config('client_secret'))
            ->asForm()->connectTimeout(5)->timeout(15)
            ->post('/v1/oauth2/token', ['grant_type' => 'client_credentials']);

        if (! $token->successful()) {
            throw new RuntimeException('PayPal authentication failed: '.($token->json('error_description') ?? 'HTTP '.$token->status()));
        }

        return Http::baseUrl($this->baseUrl())->withToken($token->json('access_token'))->acceptJson()->asJson()->connectTimeout(5)->timeout(20);
    }

    private function value(Payment $payment): string
    {
        return $this->decimalAmount($payment, in_array($payment->currency, self::NO_DECIMALS, true) ? 0 : 2);
    }

    public function start(Payment $payment): Response
    {
        $response = $this->client()
            ->withHeaders(['PayPal-Request-Id' => $payment->reference])
            ->post('/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $payment->reference,
                    'custom_id' => $payment->reference,
                    'invoice_id' => $payment->reference,
                    'description' => $this->itemName($payment),
                    'amount' => ['currency_code' => $payment->currency, 'value' => $this->value($payment)],
                ]],
                'payment_source' => ['paypal' => ['experience_context' => [
                    'brand_name' => mb_substr((string) setting('site_name', config('app.name')), 0, 127),
                    'user_action' => 'PAY_NOW',
                    'shipping_preference' => 'NO_SHIPPING',
                    'return_url' => $this->returnUrl($payment),
                    'cancel_url' => $this->cancelUrl($payment),
                ]]],
            ]);

        $approve = collect($response->json('links', []))->firstWhere('rel', 'payer-action')['href'] ?? null;
        if (! $response->successful() || ! $approve) {
            throw new RuntimeException('PayPal: '.($response->json('details.0.description') ?? $response->json('message') ?? 'HTTP '.$response->status()));
        }

        $payment->update(['gateway_reference' => $response->json('id')]);

        return redirect()->away($approve);
    }

    public function handleReturn(Request $request, Payment $payment): void
    {
        $orderId = (string) $request->query('token');
        if ($orderId === '' || $orderId !== $payment->gateway_reference) {
            return;
        }

        $client = $this->client();
        $order = $client->withHeaders(['PayPal-Request-Id' => $payment->reference.'-capture'])
            ->post("/v2/checkout/orders/{$orderId}/capture", (object) []);

        if ($order->status() === 422 && $order->json('details.0.issue') === 'ORDER_ALREADY_CAPTURED') {
            $order = $client->get("/v2/checkout/orders/{$orderId}");
        }

        $capture = $order->json('purchase_units.0.payments.captures.0');
        if ($order->json('status') === 'COMPLETED' && $capture) {
            $this->applyCapture($capture, $payment);
        } elseif ($order->json('details.0.issue') === 'INSTRUMENT_DECLINED') {
            $payment->markFailed('PayPal declined the payment method.');
        }
    }

    public function handleWebhook(Request $request): Response
    {
        $event = $request->json()->all();
        if (! $this->verifyWebhook($request, $event)) {
            return response('Invalid signature', 400);
        }

        $capture = $event['resource'] ?? [];
        $payment = Payment::where('gateway', $this->key())->where('reference', $capture['custom_id'] ?? '')->first();
        if ($payment) {
            match ($event['event_type'] ?? null) {
                'PAYMENT.CAPTURE.COMPLETED' => $this->applyCapture($capture, $payment),
                'PAYMENT.CAPTURE.DENIED' => $payment->markFailed('PayPal denied the payment.'),
                default => null,
            };
        }

        return response('OK');
    }

    private function applyCapture(array $capture, Payment $payment): void
    {
        if (($capture['status'] ?? null) === 'COMPLETED'
            && ($capture['amount']['currency_code'] ?? null) === $payment->currency
            && (float) ($capture['amount']['value'] ?? 0) + 0.001 >= (float) $this->value($payment)) {
            $payment->markPaid($capture['id'] ?? null, ['paypal_order' => $payment->gateway_reference]);
        }
    }

    /** https://developer.paypal.com/api/rest/webhooks/rest/#link-verifysignature */
    private function verifyWebhook(Request $request, array $event): bool
    {
        $webhookId = $this->config('webhook_id');
        if (! $webhookId) {
            return false;
        }

        $result = $this->client()->post('/v1/notifications/verify-webhook-signature', [
            'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
            'cert_url' => $request->header('PAYPAL-CERT-URL'),
            'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
            'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
            'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
            'webhook_id' => $webhookId,
            'webhook_event' => $event,
        ]);

        return $result->json('verification_status') === 'SUCCESS';
    }
}
