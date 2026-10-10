<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\Package;

class BookingController extends Controller
{
    /** Children (under 12) are quoted at 70% of the adult price. */
    public const CHILD_RATE = 0.7;

    public static function estimate(Package $package, int $adults, int $children): float
    {
        return round($package->price * ($adults + $children * self::CHILD_RATE), 2);
    }

    /**
     * The currency the guest was browsing in, so quotes and payments can use it.
     *
     * @return array{currency: string, exchange_rate: float}
     */
    public static function currencySnapshot(): array
    {
        $currency = currencies()->current();

        return ['currency' => $currency->code, 'exchange_rate' => currencies()->rate($currency) ?: 1];
    }

    public function store(BookingRequest $request, Package $package)
    {
        abort_unless($package->is_published, 404);

        $data = $request->validated();
        $data['children'] ??= 0;

        $booking = $package->bookings()->create($data + [
            'total_estimate' => self::estimate($package, $data['adults'], $data['children']),
        ] + self::currencySnapshot());

        // The reference doubles as the access token for the confirmation page.
        session()->push('bookings', $booking->reference);

        return redirect()->route('bookings.show', $booking);
    }

    public function show(Booking $booking)
    {
        abort_unless(PaymentController::canAccess(request(), $booking), 404);

        return view('bookings.show', ['booking' => $booking->load('package')]);
    }
}
