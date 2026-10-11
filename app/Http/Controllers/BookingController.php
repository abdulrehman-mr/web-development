<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\Package;
use App\Support\BookingPricing;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookingController extends Controller
{
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

    /**
     * Price the request on the server (never trusting the browser's total) and save it.
     *
     * @param  array<string, mixed>  $data  Validated BookingRequest data
     */
    public static function createBooking(Package $package, array $data): Booking
    {
        $children = (int) ($data['children'] ?? 0);
        $quote = app(BookingPricing::class)->quote(
            $package, (int) $data['adults'], $children,
            isset($data['room_type_id']) ? (int) $data['room_type_id'] : null,
            array_values(array_unique($data['extras'] ?? [])),
        );

        return $package->bookings()->create([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'country' => $data['country'] ?? null,
            'adults' => (int) $data['adults'],
            'children' => $children,
            'start_date' => $data['start_date'],
            'special_requests' => $data['special_requests'] ?? null,
            'room_type_id' => $quote['room']['id'] ?? null,
            'room_preference' => $quote['room']['name'] ?? null,
            'extras' => array_map(fn (array $extra) => ['name' => $extra['name'], 'price' => $extra['price'], 'per' => $extra['per']], $quote['extras']),
            'price_breakdown' => $quote['lines'],
            'total_estimate' => $quote['total'],
        ] + self::currencySnapshot());
    }

    /** The configurator page: travellers, room, extras and details with a live summary. */
    public function create(Package $package, BookingPricing $pricing): View
    {
        abort_unless($package->is_published, 404);

        return view('packages.book', [
            'package' => $package,
            'pricing' => $pricing->config($package),
            'countries' => config('countries'),
        ]);
    }

    public function store(BookingRequest $request, Package $package): RedirectResponse
    {
        abort_unless($package->is_published, 404);

        $booking = self::createBooking($package, $request->validated());

        // The reference doubles as the access token for the confirmation page.
        session()->push('bookings', $booking->reference);

        return redirect()->route('bookings.show', $booking);
    }

    public function show(Booking $booking): View
    {
        abort_unless(PaymentController::canAccess(request(), $booking), 404);

        return view('bookings.show', ['booking' => $booking->load('package')]);
    }
}
