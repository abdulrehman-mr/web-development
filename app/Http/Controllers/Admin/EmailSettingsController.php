<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TemplateMail;
use App\Models\Setting;
use App\Support\MailSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class EmailSettingsController extends Controller
{
    use Concerns;

    public function edit(): View
    {
        return view('admin.email-settings', ['drivers' => MailSettings::DRIVERS]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mail_driver' => ['required', Rule::in(array_keys(MailSettings::DRIVERS))],
            'mail_host' => 'nullable|required_if:mail_driver,smtp|string|max:255',
            'mail_port' => 'nullable|required_if:mail_driver,smtp|integer|between:1,65535',
            'mail_encryption' => 'nullable|in:tls,ssl,none',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:500',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:120',
            'mail_reply_to' => 'nullable|email|max:255',
            'mail_admin_address' => 'nullable|email|max:255',
            'email_logo_width' => 'nullable|integer|between:60,400',
            'email_logo_align' => 'nullable|in:left,center',
            'email_footer' => 'nullable|string|max:1000',
        ] + $this->imageRules('email_logo'));

        Setting::put([
            'mail_enabled' => $request->boolean('mail_enabled') ? '1' : '0',
            'mail_driver' => $data['mail_driver'],
            'mail_host' => $data['mail_host'] ?? null,
            'mail_port' => $data['mail_port'] ?? null,
            'mail_encryption' => $data['mail_encryption'] ?? 'tls',
            'mail_username' => $data['mail_username'] ?? null,
            'mail_from_address' => $data['mail_from_address'],
            'mail_from_name' => $data['mail_from_name'],
            'mail_reply_to' => $data['mail_reply_to'] ?? null,
            'mail_admin_address' => $data['mail_admin_address'] ?? null,
            'email_logo' => $this->imageFrom($request, 'email_logo', setting('email_logo')),
            'email_logo_width' => $data['email_logo_width'] ?? 160,
            'email_logo_align' => $data['email_logo_align'] ?? 'left',
            'email_footer' => $data['email_footer'] ?? null,
        ]);

        // Blank keeps the saved password; it is never sent back to the browser.
        if (filled($data['mail_password'] ?? null)) {
            Setting::putSecret('mail_password', $data['mail_password']);
        } elseif ($request->boolean('clear_mail_password')) {
            Setting::putSecret('mail_password', null);
        }

        return back()->with('status', 'Email settings saved.');
    }

    /** Sends a sample email with the saved settings and reports the exact error if it fails. */
    public function test(Request $request): RedirectResponse
    {
        $to = $request->validate(['test_to' => 'required|email'])['test_to'];
        MailSettings::apply();

        try {
            Mail::to($to)->send(new TemplateMail('test', 'Test email from '.setting('site_name', config('app.name')),
                '<p>Hello!</p><p>This is a test email. If you can read it, your email settings work.</p><p><strong>Sending method:</strong> '.e(MailSettings::DRIVERS[config('mail.default')] ?? config('mail.default')).'</p>'));
        } catch (Throwable $e) {
            return back()->withErrors(['test_to' => 'Sending failed: '.$e->getMessage()])->withInput();
        }

        $note = config('mail.default') === 'log' ? ' (written to storage/logs, because the method is “Don’t send”)' : '';

        return back()->with('status', "Test email sent to {$to}{$note}.");
    }
}
