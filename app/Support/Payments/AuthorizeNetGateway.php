<?php

namespace App\Support\Payments;

use App\Models\Payment;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authorize.net Accept Hosted payment form.
 * Docs: https://developer.authorize.net/api/reference/features/accept-hosted.html
 * Payments are confirmed by the signed webhook, then re-checked with getTransactionDetails.
 */
class AuthorizeNetGateway extends Gateway
{
    public function key(): string
    {
        return 'authorizenet';
    }

    public function name(): string
    {
        return 'Authorize.net';
    }

    public function description(): string
    {
        return 'Credit and debit cards';
    }

    public function fields(): array
    {
        return [
            'mode' => ['label' => 'Mode', 'type' => 'select', 'options' => ['sandbox' => 'Sandbox (testing)', 'live' => 'Live']],
            'login_id' => ['label' => 'API Login ID', 'type' => 'text', 'required' => true],
            'transaction_key' => ['label' => 'Transaction key', 'type' => 'secret', 'required' => true],
            'signature_key' => ['label' => 'Signature key', 'type' => 'secret', 'required' => true, 'help' => 'Account → Settings → API Credentials & Keys. Also add the webhook URL below (event: authcapture.created).'],
            'currency' => ['label' => 'Account currency', 'type' => 'text', 'help' => 'The currency of your merchant account, e.g. USD or CAD. Payments are charged in this currency.'],
        ];
    }

    public function supportedCurrencies(): ?array
    {
        return [strtoupper($this->config('currency') ?: 'USD')];
    }

    private function apiUrl(): string
    {
        return $this->sandbox() ? 'https://apitest.authorize.net/xml/v1/request.api' : 'https://api.authorize.net/xml/v1/request.api';
    }

    private function formUrl(): string
    {
        return $this->sandbox() ? 'https://test.authorize.net/payment/payment' : 'https://accept.authorize.net/payment/payment';
    }

    /** @return array<string, mixed> */
    private function call(string $request, array $body): array
    {
        $response = Http::connectTimeout(5)->timeout(20)->asJson()->post($this->apiUrl(), [$request => [
            'merchantAuthentication' => ['name' => $this->config('login_id'), 'transactionKey' => $this->config('transaction_key')],
        ] + $body]);

        return $this->decode($response);
    }

    /** Authorize.net prefixes its JSON with a byte-order mark. */
    private function decode(HttpResponse $response): array
    {
        return json_decode(preg_replace('/^\xEF\xBB\xBF/', '', $response->body()), true) ?? [];
    }

    public function start(Payment $payment): Response
    {
        $result = $this->call('getHostedPaymentPageRequest', [
            'transactionRequest' => [
                'transactionType' => 'authCaptureTransaction',
                'amount' => $this->decimalAmount($payment, 2),
                'order' => ['invoiceNumber' => $payment->reference, 'description' => mb_substr($this->itemName($payment), 0, 255)],
                'customer' => ['email' => $payment->booking->email],
            ],
            'hostedPaymentSettings' => ['setting' => [
                ['settingName' => 'hostedPaymentReturnOptions', 'settingValue' => json_encode([
                    'showReceipt' => false,
                    'url' => $this->returnUrl($payment),
                    'urlText' => 'Continue',
                    'cancelUrl' => $this->cancelUrl($payment),
                    'cancelUrlText' => 'Cancel',
                ])],
                ['settingName' => 'hostedPaymentButtonOptions', 'settingValue' => json_encode(['text' => 'Pay '.$payment->formattedAmount()])],
                ['settingName' => 'hostedPaymentOrderOptions', 'settingValue' => json_encode(['show' => true, 'merchantName' => (string) setting('site_name')])],
            ]],
        ]);

        if (($result['messages']['resultCode'] ?? null) !== 'Ok' || empty($result['token'])) {
            throw new RuntimeException('Authorize.net: '.($result['messages']['message'][0]['text'] ?? 'could not start checkout'));
        }

        return $this->postRedirect($this->formUrl(), ['token' => $result['token']]);
    }

    /** Accept Hosted does not tell us the result on return; the webhook confirms the payment. */
    public function handleReturn(Request $request, Payment $payment): void {}

    public function handleWebhook(Request $request): Response
    {
        if (! $this->validSignature($request->getContent(), (string) $request->header('X-ANET-Signature'))) {
            return response('Invalid signature', 400);
        }

        if ($request->json('eventType') !== 'net.authorize.payment.authcapture.created') {
            return response('OK');
        }

        // Never trust the webhook body alone: fetch the transaction from the API.
        $details = $this->call('getTransactionDetailsRequest', ['transId' => (string) $request->json('payload.id')]);
        $transaction = $details['transaction'] ?? [];
        $payment = Payment::where('gateway', $this->key())->where('reference', $transaction['order']['invoiceNumber'] ?? '')->first();

        if ($payment && (int) ($transaction['responseCode'] ?? 0) === 1
            && (float) ($transaction['authAmount'] ?? 0) + 0.001 >= (float) $payment->amount) {
            $payment->markPaid((string) $transaction['transId']);
        }

        return response('OK');
    }

    /**
     * X-ANET-Signature is "sha512=" + HMAC-SHA512 of the body keyed with the Signature Key.
     * Authorize.net's samples use the key both as text and hex-decoded, so accept either.
     */
    private function validSignature(string $body, string $header): bool
    {
        $key = (string) $this->config('signature_key');
        $signature = strtolower((string) preg_replace('/^sha512=/i', '', $header));
        if ($key === '' || $signature === '') {
            return false;
        }

        $candidates = [hash_hmac('sha512', $body, $key)];
        if (ctype_xdigit($key) && strlen($key) % 2 === 0) {
            $candidates[] = hash_hmac('sha512', $body, hex2bin($key));
        }

        foreach ($candidates as $expected) {
            if (hash_equals($expected, $signature)) {
                return true;
            }
        }

        return false;
    }
}
