<?php

namespace App\Support\Payments;

use App\Models\Payment;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/** Stripe Checkout (hosted payment page). Docs: https://docs.stripe.com/api/checkout/sessions */
class StripeGateway extends Gateway
{
    private const API = 'https://api.stripe.com/v1';

    /** Seconds a webhook timestamp may differ from now. */
    private const SIGNATURE_TOLERANCE = 300;

    public function key(): string
    {
        return 'stripe';
    }

    public function name(): string
    {
        return 'Stripe';
    }

    public function description(): string
    {
        return 'Cards, Apple Pay and Google Pay';
    }

    public function fields(): array
    {
        return [
            'secret_key' => ['label' => 'Secret key', 'type' => 'secret', 'required' => true, 'help' => 'sk_test_… for testing, sk_live_… for real payments.'],
            'webhook_secret' => ['label' => 'Webhook signing secret', 'type' => 'secret', 'help' => 'whsec_… — add the webhook URL below in Stripe and select checkout.session.* events.'],
        ];
    }

    public function sandbox(): bool
    {
        return ! str_starts_with((string) $this->config('secret_key'), 'sk_live_');
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(self::API)->withToken((string) $this->config('secret_key'))->asForm()->connectTimeout(5)->timeout(20);
    }

    /** Stripe wants the smallest currency unit; three-decimal currencies must end in 0. */
    private function minorAmount(Payment $payment): int
    {
        $decimals = currencies()->find($payment->currency)?->decimals ?? 2;
        $minor = (int) round((float) $payment->amount * 10 ** $decimals);

        return $decimals === 3 ? (int) (round($minor / 10) * 10) : $minor;
    }

    public function start(Payment $payment): Response
    {
        $response = $this->client()
            ->withHeaders(['Idempotency-Key' => $payment->reference])
            ->post('/checkout/sessions', [
                'mode' => 'payment',
                'client_reference_id' => $payment->reference,
                'customer_email' => $payment->booking->email,
                'success_url' => $this->returnUrl($payment).'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $this->cancelUrl($payment),
                'metadata' => ['payment' => $payment->reference, 'booking' => $payment->booking->reference],
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => strtolower($payment->currency),
                        'unit_amount' => $this->minorAmount($payment),
                        'product_data' => ['name' => $this->itemName($payment)],
                    ],
                ]],
            ]);

        if (! $response->successful() || ! $response->json('url')) {
            throw new RuntimeException('Stripe: '.($response->json('error.message') ?? 'HTTP '.$response->status()));
        }

        $payment->update(['gateway_reference' => $response->json('id')]);

        return redirect()->away($response->json('url'));
    }

    public function handleReturn(Request $request, Payment $payment): void
    {
        $sessionId = (string) $request->query('session_id', $payment->gateway_reference);
        if ($sessionId === '' || $sessionId !== $payment->gateway_reference) {
            return;
        }

        $session = $this->client()->get('/checkout/sessions/'.urlencode($sessionId));
        $this->applySession($session->json() ?? []);
    }

    public function handleWebhook(Request $request): Response
    {
        if (! $this->validSignature($request->getContent(), (string) $request->header('Stripe-Signature'))) {
            return response('Invalid signature', 400);
        }

        $event = $request->json()->all();
        $session = $event['data']['object'] ?? [];

        match ($event['type'] ?? null) {
            'checkout.session.completed', 'checkout.session.async_payment_succeeded' => $this->applySession($session),
            'checkout.session.async_payment_failed' => $this->findPayment($session)?->markFailed('Stripe reported the payment failed.'),
            'checkout.session.expired' => $this->findPayment($session)?->markFailed('Checkout session expired.', Payment::CANCELLED),
            default => null,
        };

        return response('OK');
    }

    private function applySession(array $session): void
    {
        $payment = $this->findPayment($session);
        if ($payment && ($session['payment_status'] ?? null) === 'paid'
            && (int) ($session['amount_total'] ?? -1) === $this->minorAmount($payment)
            && strtoupper($session['currency'] ?? '') === $payment->currency) {
            $payment->markPaid($session['payment_intent'] ?? $session['id'], ['stripe_session' => $session['id'] ?? null]);
        }
    }

    private function findPayment(array $session): ?Payment
    {
        $reference = $session['client_reference_id'] ?? null;

        return $reference ? Payment::where('gateway', $this->key())->where('reference', $reference)->first() : null;
    }

    /** https://docs.stripe.com/webhooks#verify-manually */
    private function validSignature(string $payload, string $header): bool
    {
        $secret = $this->config('webhook_secret');
        if (! $secret || $header === '') {
            return false;
        }

        $parts = [];
        foreach (explode(',', $header) as $item) {
            [$k, $v] = array_pad(explode('=', trim($item), 2), 2, null);
            $parts[$k][] = $v;
        }
        $timestamp = (int) ($parts['t'][0] ?? 0);
        if (abs(time() - $timestamp) > self::SIGNATURE_TOLERANCE) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
        foreach ($parts['v1'] ?? [] as $signature) {
            if (hash_equals($expected, (string) $signature)) {
                return true;
            }
        }

        return false;
    }
}
