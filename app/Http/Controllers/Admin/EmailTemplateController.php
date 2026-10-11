<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Support\EmailNotifier;
use App\Support\MailSettings;
use Database\Seeders\EmailTemplateSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Throwable;

class EmailTemplateController extends Controller
{
    public function index(): View
    {
        return view('admin.email-templates.index', [
            'templates' => EmailTemplate::orderBy('id')->get(),
            'mailEnabled' => MailSettings::enabled(),
        ]);
    }

    public function edit(EmailTemplate $template): View
    {
        return view('admin.email-templates.edit', [
            'template' => $template,
            'placeholders' => EmailNotifier::PLACEHOLDERS,
            'hasDefault' => isset(EmailTemplateSeeder::DEFAULTS[$template->key]),
        ]);
    }

    public function update(Request $request, EmailTemplate $template): RedirectResponse
    {
        $template->update($this->validated($request) + ['is_enabled' => $request->boolean('is_enabled')]);

        return back()->with('status', 'Template saved.');
    }

    public function toggle(EmailTemplate $template): RedirectResponse
    {
        $template->update(['is_enabled' => ! $template->is_enabled]);

        return back()->with('status', "“{$template->name}” ".($template->is_enabled ? 'enabled.' : 'disabled.'));
    }

    /** Renders unsaved edits with sample data, shown in the editor's preview frame. */
    public function preview(Request $request, EmailTemplate $template, EmailNotifier $notifier): Response
    {
        $draft = $template->replicate()->fill($this->validated($request));
        $rendered = $notifier->render($draft);

        return response(view('emails.layout', ['html' => $rendered['html'], 'preheader' => ''])->render())
            ->header('X-Email-Subject', rawurlencode($rendered['subject']));
    }

    public function test(Request $request, EmailTemplate $template, EmailNotifier $notifier): RedirectResponse
    {
        $to = $request->validate(['test_to' => 'required|email'])['test_to'];

        try {
            $notifier->sendTest($template, $to);
        } catch (Throwable $e) {
            return back()->withErrors(['test_to' => 'Sending failed: '.$e->getMessage()]);
        }

        return back()->with('status', "Test sent to {$to} with sample details.");
    }

    public function reset(EmailTemplate $template): RedirectResponse
    {
        $default = EmailTemplateSeeder::DEFAULTS[$template->key] ?? null;
        abort_unless($default, 404);
        $template->update(['subject' => $default['subject'], 'body' => $default['body']]);

        return back()->with('status', 'Template reset to the default wording.');
    }

    /** @return array{subject: string, body: string} */
    private function validated(Request $request): array
    {
        return $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:20000',
        ]);
    }
}
