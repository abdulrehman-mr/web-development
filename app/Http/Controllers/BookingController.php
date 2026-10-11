<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\Departure;
use App\Models\Package;
use App\Support\BookingPricing;
use App\Support\EmailNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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
        // Locked so two guests can't take the last seats of a departure at the same time.
        $booking = DB::transaction(function () use ($package, $data) {
            $children = (int) ($data['children'] ?? 0);
            $departure = self::departureFor($package, $data, (int) $data['adults'] + $children);
            $quote = app(BookingPricing::class)->quote(
                $package, (int) $data['adults'], $children,
                isset($data['room_type_id']) ? (int) $data['room_type_id'] : null,
                array_values(array_unique($data['extras'] ?? [])),
                array_map('strval', $data['options'] ?? []),
                $departure,
            );

            return self::saveBooking($package, $data, $children, $departure, $quote);
        });

        $notifier = app(EmailNotifier::class);
        $notifier->forBooking('booking_received_guest', $booking);
        $notifier->forBooking('booking_received_admin', $booking);

        return $booking;
    }

    /**
     * The departure the guest picked, checked against the journey's date rules and seats left.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    private static function departureFor(Package $package, array $data, int $travellers): ?Departure
    {
        // With no upcoming departures scheduled, guests can still ask for their own date.
        if (! $package->usesDepartures() || ! $package->departures()->upcoming()->exists()) {
            if (empty($data['start_date'])) {
                throw ValidationException::withMessages(['start_date' => 'Please choose a start date.']);
            }

            return null;
        }

        if (empty($data['departure_id'])) {
            if ($package->date_mode === 'departures' || empty($data['start_date'])) {
                throw ValidationException::withMessages(['departure_id' => 'Please choose one of the departures.']);
            }

            return null;
        }

        $departure = $package->departures()->upcoming()->whereKey($data['departure_id'])->lockForUpdate()->first();
        if (! $departure) {
            throw ValidationException::withMessages(['departure_id' => 'That departure is no longer available. Please choose another.']);
        }
        $left = $departure->seatsLeft();
        if ($left !== null && $travellers > $left) {
            throw ValidationException::withMessages(['departure_id' => $left === 0
                ? 'That departure is full. Please choose another.'
                : "Only {$left} ".str('seat')->plural($left).' left on that departure.']);
        }

        return $departure;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $quote
     */
    private static function saveBooking(Package $package, array $data, int $children, ?Departure $departure, array $quote): Booking
    {
        return $package->bookings()->create([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'country' => $data['country'] ?? null,
            'adults' => (int) $data['adults'],
            'children' => $children,
            'departure_id' => $departure?->id,
            'start_date' => $departure ? $departure->starts_on->toDateString() : $data['start_date'],
            'special_requests' => $data['special_requests'] ?? null,
            'room_type_id' => $quote['room']['id'] ?? null,
            'room_preference' => $quote['room']['name'] ?? null,
            'extras' => array_map(fn (array $extra) => ['name' => $extra['name'], 'price' => $extra['price'], 'per' => $extra['per']], $quote['extras']),
            'options' => $quote['options'],
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
