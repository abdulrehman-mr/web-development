<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Support\EmailNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        return view('admin.bookings.index', [
            'bookings' => Booking::with('package')
                ->when(in_array($status, Booking::STATUSES, true), fn ($q) => $q->where('status', $status))
                ->when($request->query('q'), fn ($q, $t) => $q->where(fn ($w) => $w->where('reference', 'like', "%{$t}%")
                    ->orWhere('full_name', 'like', "%{$t}%")->orWhere('email', 'like', "%{$t}%")))
                ->latest()->paginate(20)->withQueryString(),
            'status' => $status,
            'counts' => Booking::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(Booking $booking)
    {
        return view('admin.bookings.show', ['booking' => $booking->load('package', 'payments')]);
    }

    public function update(Request $request, Booking $booking, EmailNotifier $notifier)
    {
        $previousStatus = $booking->status;
        $booking->update($request->validate([
            'status' => ['required', Rule::in(Booking::STATUSES)],
            'admin_notes' => 'nullable|string|max:5000',
            'total_estimate' => 'nullable|numeric|min:0',
        ]));

        $message = 'Booking updated.';
        if ($booking->status !== $previousStatus && in_array($booking->status, ['confirmed', 'cancelled'], true)) {
            $sent = $notifier->forBooking("booking_{$booking->status}_guest", $booking->load('package'));
            $message .= $sent ? ' The guest has been emailed.' : '';
        }

        return back()->with('status', $message);
    }

    public function sendPaymentLink(Booking $booking, EmailNotifier $notifier): RedirectResponse
    {
        return $notifier->forBooking('payment_link_guest', $booking->load('package'))
            ? back()->with('status', "Payment link emailed to {$booking->email}.")
            : back()->withErrors(['email' => 'The email could not be sent. Check Admin → Email settings, and that the “Payment link” template is enabled.']);
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();

        return redirect()->route('admin.bookings.index')->with('status', 'Booking deleted.');
    }
}
