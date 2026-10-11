<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\Page;
use App\Support\EmailNotifier;
use App\Support\PageBuilder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(PageBuilder $builder): View
    {
        return $builder->view(Page::system('about'));
    }

    public function stays(PageBuilder $builder): View
    {
        return $builder->view(Page::system('stays'));
    }

    public function contact(PageBuilder $builder): View
    {
        return $builder->view(Page::system('contact'));
    }

    /** A page made in Admin → Pages. Drafts are visible to signed-in admins only. */
    public function show(Page $page, PageBuilder $builder): View
    {
        abort_if($page->is_system, 404);
        abort_unless($page->is_published || auth()->check(), 404);

        return $builder->view($page);
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
