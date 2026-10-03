<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\Inquiry;
use App\Models\Package;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('admin.dashboard', [
            'stats' => [
                ['Pending bookings', Booking::where('status', 'pending')->count(), route('admin.bookings.index', ['status' => 'pending'])],
                ['Confirmed bookings', Booking::where('status', 'confirmed')->count(), route('admin.bookings.index', ['status' => 'confirmed'])],
                ['Unread enquiries', Inquiry::where('is_read', false)->count(), route('admin.inquiries.index')],
                ['Published journeys', Package::published()->count(), route('admin.packages.index')],
            ],
            'pipeline' => (float) Booking::whereIn('status', ['pending', 'confirmed'])->sum('total_estimate'),
            'destinationsCount' => Destination::count(),
            'recentBookings' => Booking::with('package')->latest()->take(8)->get(),
            'recentInquiries' => Inquiry::latest()->take(5)->get(),
        ]);
    }
}
