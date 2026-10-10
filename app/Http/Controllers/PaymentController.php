<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Support\Payments\PaymentGateways;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(private PaymentGateways $gateways) {}

    /** Guests reach this page from their confirmation page or a signed link the team sends them. */
    public static function canAccess(Request $request, Booking $booking): bool
    {
        return in_array($booking->reference, session('bookings', []), true)
            || $request->hasValidSignature()
            || auth()->check();
    }

    /** Signed link to a booking's payment page, valid for 30 days. */
    public static function payLink(Booking $booking): string
    {
        return URL::temporarySignedRoute('bookings.pay', now()->addDays(30), $booking);
    }

    public function show(Request $request, Booking $booking): View
    {
        abort_unless(self::canAccess($request, $booking), 404);
        // Remember access so the guest can come back from the payment provider.
        session()->push('bookings', $booking->reference);

        return view('payments.show', [
            'booking' => $booking->load('package', 'payments'),
            'gateways' => $this->gateways->enabled(),
        ]);
    }

    public function start(Request $request, Booking $booking): Response
    {
        abort_unless(self::canAccess($request, $booking), 404);

        $data = $request->validate([
            'gateway' => ['required', Rule::in($this->gateways->enabled()->keys())],
            'type' => ['required', Rule::in(['deposit', 'full'])],
        ]);

        $baseAmount = $data['type'] === 'deposit' && $booking->amount_paid <= 0 ? $booking->depositAmount() : $booking->balanceDue();
        if ($baseAmount <= 0) {
            return back()->with('status', 'This booking is already paid in full.');
        }

        $gateway = $this->gateways->find($data['gateway']);
        $currency = $gateway->chargeCurrency($booking);
        $rate = $currency === $booking->currency ? $booking->exchange_rate : currencies()->rate(currencies()->find($currency));

        $payment = $booking->payments()->create([
            'gateway' => $gateway->key(),
            'type' => $data['type'],
            'base_amount' => $baseAmount,
            'currency' => $currency,
            'amount' => round($baseAmount * ($rate ?: 1), 2),
        ]);

        try {
            return $gateway->start($payment);
        } catch (Throwable $e) {
            report($e);
            $payment->markFailed($e->getMessage());

            return back()->withErrors(['gateway' => "We couldn't start {$gateway->name()} right now. Please try another method or contact us."]);
        }
    }

    public function return(Request $request, Payment $payment): RedirectResponse
    {
        $gateway = $this->gateways->find($payment->gateway);

        try {
            $gateway?->handleReturn($request, $payment);
        } catch (Throwable $e) {
            report($e);
        }

        $payment->refresh();
        $message = $payment->status === Payment::PAID
            ? 'Payment received — thank you! A receipt is on its way to your inbox.'
            : 'Thank you! We are confirming your payment with '.($gateway?->name() ?? 'the provider').'. This page will show it as paid once confirmed.';

        return redirect(self::payLink($payment->booking))->with('status', $message);
    }

    public function cancel(Payment $payment): RedirectResponse
    {
        $payment->markFailed('Cancelled by the guest.', Payment::CANCELLED);

        return redirect(self::payLink($payment->booking))->with('status', 'Payment cancelled. You can choose another method below.');
    }

    public function webhook(Request $request, string $gateway): Response
    {
        $driver = $this->gateways->find($gateway);
        abort_unless($driver, 404);

        return $driver->handleWebhook($request);
    }
}
