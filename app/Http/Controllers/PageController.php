<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Hotel;
use App\Models\Inquiry;
use App\Models\Testimonial;
use App\Support\EmailNotifier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    public function about()
    {
        return view('pages.about', [
            'testimonials' => Testimonial::where('is_published', true)->orderBy('sort_order')->take(3)->get(),
        ]);
    }

    public function stays()
    {
        return view('pages.stays', [
            'hotels' => Hotel::with('destination')->orderByDesc('stars')->orderBy('name')->get()->groupBy('type'),
        ]);
    }

    public function contact()
    {
        return view('pages.contact', [
            'faqs' => Faq::where('is_published', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function sendContact(Request $request)
    {
        // Honeypot: real visitors never fill the hidden "website" field.
        if (filled($request->input('website'))) {
            return back()->with('status', 'Thank you — we will be in touch shortly.');
        }

        $inquiry = Inquiry::create($request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:160',
            'phone' => 'nullable|string|max:40',
            'country' => ['nullable', 'string', Rule::in(array_values(config('countries')))],
            'subject' => 'nullable|string|max:160',
            'message' => 'required|string|max:5000',
        ]));
        $notifier = app(EmailNotifier::class);
        $notifier->forInquiry('inquiry_received_admin', $inquiry);
        $notifier->forInquiry('inquiry_autoreply_guest', $inquiry);

        return back()->with('status', 'Thank you — a travel designer will reply within one working day.');
    }
}
