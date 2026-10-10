<?php

namespace App\Support\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 2Checkout (Verifone) ConvertPlus hosted checkout with a signed dynamic-product buy link.
 * Payments are confirmed by the signed IPN.
 * Docs: https://verifone.cloud/docs/2checkout/Documentation/07Commerce/2Checkout-ConvertPlus/ConvertPlus_Buy-Links_Signature
 */
class TwoCheckoutGateway extends Gateway
{
    private const CHECKOUT_URL = 'https://secure.2checkout.com/checkout/buy';

    /** Buy-link parameters that are part of the signature. */
    private const SIGNED = [
        'return-url', 'return-type', 'expiration', 'order-ext-ref', 'item-ext-ref', 'lock', 'cust-params',
        'customer-ref', 'customer-ext-ref', 'currency', 'prod', 'price', 'qty', 'tangible', 'type', 'opt',
        'coupon', 'description', 'recurrence', 'duration', 'renewal-price',
    ];

    /** IPN order statuses that mean the money is secured. */
    private const PAID_STATUSES = ['COMPLETE', 'PAYMENT_AUTHORIZED'];

    public function key(): string
    {
        return '2checkout';
    }

    public function name(): string
    {
        return '2Checkout';
    }

    public function description(): string
    {
        return 'Cards and local payment methods (Verifone)';
    }

    public function fields(): array
    {
        return [
            'mode' => ['label' => 'Mode', 'type' => 'select', 'options' => ['sandbox' => 'Test orders', 'live' => 'Live']],
            'merchant_code' => ['label' => 'Merchant code', 'type' => 'text', 'required' => true],
            'secret_word' => ['label' => 'Buy-link secret word', 'type' => 'secret', 'required' => true, 'help' => 'Integrations → Webhooks & API → Secret word.'],
            'secret_key' => ['label' => 'Secret key', 'type' => 'secret', 'required' => true, 'help' => 'Integrations → Webhooks & API → Secret key. Also set the IPN URL below and enable SHA256.'],
        ];
    }

    /** Length-prefix each value (in bytes) and concatenate, as 2Checkout's signatures require. */
    public static function serialize(array $values): string
    {
        $out = '';
        foreach ($values as $value) {
            if (is_array($value)) {
                $out .= self::serialize($value);
            } else {
                $out .= strlen((string) $value).$value;
            }
        }

        return $out;
    }

    /** @param array<string, string> $params */
    public function buyLinkSignature(array $params): string
    {
        $signed = array_intersect_key($params, array_flip(self::SIGNED));
        ksort($signed);

        return hash_hmac('sha256', self::serialize($signed), (string) $this->config('secret_word'));
    }

    public function start(Payment $payment): Response
    {
        $params = [
            'merchant' => (string) $this->config('merchant_code'),
            'dynamic' => '1',
            'prod' => $this->itemName($payment),
            'price' => $this->decimalAmount($payment, 2),
            'qty' => '1',
            'type' => 'PRODUCT',
            'tangible' => '0',
            'currency' => $payment->currency,
            'order-ext-ref' => $payment->reference,
            'return-url' => $this->returnUrl($payment),
            'return-type' => 'redirect',
            'email' => $payment->booking->email,
            'name' => $payment->booking->full_name,
        ];
        if ($this->sandbox()) {
            $params['test'] = '1';
        }
        $params['signature'] = $this->buyLinkSignature($params);

        $payment->update(['gateway_reference' => null]);

        return redirect()->away(self::CHECKOUT_URL.'?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986));
    }

    /** The IPN confirms the order; the return redirect is not trusted. */
    public function handleReturn(Request $request, Payment $payment): void {}

    public function handleWebhook(Request $request): Response
    {
        $data = $request->post();
        $secret = (string) $this->config('secret_key');
        $received = strtolower((string) ($data['SIGNATURE_SHA2_256'] ?? ''));
        $signed = array_diff_key($data, array_flip(['HASH', 'SIGNATURE_SHA2_256', 'SIGNATURE_SHA3_256']));

        if ($secret === '' || $received === '' || ! hash_equals(hash_hmac('sha256', self::serialize($signed), $secret), $received)) {
            return response('Invalid signature', 400);
        }

        $payment = Payment::where('gateway', $this->key())->where('reference', $data['REFNOEXT'] ?? '')->first();
        if ($payment && in_array($data['ORDERSTATUS'] ?? '', self::PAID_STATUSES, true)
            && strtoupper($data['CURRENCY'] ?? '') === $payment->currency
            && (float) ($data['IPN_TOTALGENERAL'] ?? 0) + 0.001 >= (float) $payment->amount) {
            $payment->markPaid((string) ($data['REFNO'] ?? ''));
        }

        // Read receipt so 2Checkout stops resending the notification.
        $date = gmdate('YmdHis');
        $hash = hash_hmac('sha256', self::serialize([
            $data['IPN_PID'][0] ?? '', $data['IPN_PNAME'][0] ?? '', $data['IPN_DATE'] ?? '', $date,
        ]), $secret);

        return response('<sig algo="sha256" date="'.$date.'">'.$hash.'</sig>');
    }
}
