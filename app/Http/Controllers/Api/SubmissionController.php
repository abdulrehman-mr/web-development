<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BookingController;
use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Models\Inquiry;
use App\Models\Package;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function booking(BookingRequest $request)
    {
        $request->validate(['package' => 'required|string|exists:packages,slug']);
        $package = Package::published()->where('slug', $request->input('package'))->firstOrFail();

        $data = $request->validated();
        $data['children'] ??= 0;

        $booking = $package->bookings()->create($data + [
            'total_estimate' => BookingController::estimate($package, $data['adults'], $data['children']),
        ] + BookingController::currencySnapshot());

        return response()->json(['data' => [
            'reference' => $booking->reference,
            'status' => $booking->status,
            'total_estimate' => (float) $booking->total_estimate,
            'currency' => currencies()->base()->code,
        ]], 201);
    }

    public function inquiry(Request $request)
    {
        $inquiry = Inquiry::create($request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:160',
            'phone' => 'nullable|string|max:40',
            'country' => 'nullable|string|max:80',
            'subject' => 'nullable|string|max:160',
            'message' => 'required|string|max:5000',
        ]));

        return response()->json(['data' => ['id' => $inquiry->id]], 201);
    }
}
