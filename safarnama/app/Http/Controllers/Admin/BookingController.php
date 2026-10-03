<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
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
        return view('admin.bookings.show', ['booking' => $booking->load('package')]);
    }

    public function update(Request $request, Booking $booking)
    {
        $booking->update($request->validate([
            'status' => ['required', Rule::in(Booking::STATUSES)],
            'admin_notes' => 'nullable|string|max:5000',
            'total_estimate' => 'nullable|numeric|min:0',
        ]));

        return back()->with('status', 'Booking updated.');
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();

        return redirect()->route('admin.bookings.index')->with('status', 'Booking deleted.');
    }
}
