<?php

namespace App\Support\Payments;

use App\Models\Payment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CoinPayments.net simple checkout: the guest chooses a cryptocurrency on CoinPayments.
 * Payments are confirmed by the HMAC-signed IPN.
 * Docs: https://www.coinpayments.net/merchant-tools-simple and https://www.coinpayments.net/merchant-tools-ipn
 */
class CoinPaymentsGateway extends Gateway
{
    private const CHECKOUT_URL = 'https://www.coinpayments.net/index.php';

    public function key(): string
    {
        return 'coinpayments';
    }

    public function name(): string
    {
        return 'CoinPayments';
    }

    public function description(): string
    {
        return 'Bitcoin, Ethereum, USDT and 100+ cryptocurrencies';
    }

    public function fields(): array
    {
        return [
            'merchant_id' => ['label' => 'Merchant ID', 'type' => 'text', 'required' => true, 'help' => 'Account → Account Settings → Merchant ID.'],
            'ipn_secret' => ['label' => 'IPN secret', 'type' => 'secret', 'required' => true, 'help' => 'Account → Account Settings → Merchant Settings → IPN Secret.'],
        ];
    }

    public function start(Payment $payment): Response
    {
        $booking = $payment->booking;

        return $this->postRedirect(self::CHECKOUT_URL, [
            'cmd' => '_pay_simple',
            'reset' => '1',
            'merchant' => (string) $this->config('merchant_id'),
            'item_name' => $this->itemName($payment),
            'item_number' => $booking->reference,
            'invoice' => $payment->reference,
            'currency' => $payment->currency,
            'amountf' => $this->decimalAmount($payment),
            'want_shipping' => '0',
            'email' => $booking->email,
            'first_name' => strtok($booking->full_name, ' ') ?: $booking->full_name,
            'last_name' => trim((string) strstr($booking->full_name, ' ')) ?: '-',
            'success_url' => $this->returnUrl($payment),
            'cancel_url' => $this->cancelUrl($payment),
            'ipn_url' => $this->webhookUrl(),
        ]);
    }

    /** Crypto payments confirm on the blockchain; the IPN marks them paid. */
    public function handleReturn(Request $request, Payment $payment): void {}

    public function handleWebhook(Request $request): Response
    {
        $secret = (string) $this->config('ipn_secret');
        $hmac = strtolower((string) $request->header('HMAC'));

        if ($secret === '' || $request->input('ipn_mode') !== 'hmac' || $hmac === ''
            || ! hash_equals(hash_hmac('sha512', $request->getContent(), $secret), $hmac)
            || $request->input('merchant') !== $this->config('merchant_id')) {
            return response('Invalid IPN', 400);
        }

        $payment = Payment::where('gateway', $this->key())->where('reference', (string) $request->input('invoice'))->first();
        if (! $payment) {
            return response('IPN OK');
        }

        $status = (int) $request->input('status');
        $amountMatches = strtoupper((string) $request->input('currency1')) === $payment->currency
            && (float) $request->input('amount1') + 0.001 >= (float) $payment->amount;

        if (($status >= 100 || $status === 2) && $amountMatches) {
            $payment->markPaid((string) $request->input('txn_id'), ['coin' => $request->input('currency2')]);
        } elseif ($status < 0) {
            $payment->markFailed('CoinPayments: '.$request->input('status_text', 'payment failed or timed out'));
        }

        return response('IPN OK');
    }
}
