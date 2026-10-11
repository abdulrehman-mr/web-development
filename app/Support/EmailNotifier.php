<?php

namespace App\Support;

use App\Http\Controllers\PaymentController;
use App\Mail\TemplateMail;
use App\Models\Booking;
use App\Models\EmailTemplate;
use App\Models\Inquiry;
use App\Models\Payment;
use App\Support\Payments\PaymentGateways;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Fills an email template with booking/enquiry details and sends it.
 * Sending never throws: a mail problem must not break a booking or payment.
 */
class EmailNotifier
{
    /** Placeholders available in every template, with a short explanation for the editor. */
    public const PLACEHOLDERS = [
        'guest_name' => 'Guest’s full name',
        'guest_email' => 'Guest’s email',
        'guest_phone' => 'Guest’s phone',
        'guest_country' => 'Country the guest travels from',
        'booking_reference' => 'Booking reference, e.g. SFR-AB12CD',
        'journey' => 'Journey title',
        'start_date' => 'Start date',
        'travellers' => 'e.g. “2 adults, 1 child”',
        'room' => 'Chosen room',
        'options' => 'Chosen options, e.g. hotel and package class',
        'extras' => 'Chosen extras',
        'special_requests' => 'Guest’s notes',
        'total' => 'Booking total (guest’s currency)',
        'amount_paid' => 'Amount paid so far',
        'balance' => 'Balance still due',
        'status' => 'Booking status',
        'payment_amount' => 'Amount of this payment',
        'payment_method' => 'Payment provider',
        'booking_link' => 'Guest’s booking page',
        'payment_link' => 'Secure payment page (valid 30 days)',
        'admin_link' => 'Admin page for this booking/enquiry',
        'inquiry_subject' => 'Enquiry topic',
        'message' => 'Enquiry message',
        'site_name' => 'Your site name',
        'contact_phone' => 'Your phone number',
        'contact_email' => 'Your email address',
        'journeys_link' => 'Link to all journeys',
    ];

    /** Send a template to a booking's guest or to the team. */
    public function forBooking(string $key, Booking $booking, ?Payment $payment = null): bool
    {
        $template = $this->template($key);
        if (! $template) {
            return false;
        }

        $to = $template->audience === 'admin' ? MailSettings::adminAddress() : $booking->email;

        return $this->send($template, $to, $this->bookingValues($booking, $payment));
    }

    public function forInquiry(string $key, Inquiry $inquiry): bool
    {
        $template = $this->template($key);
        if (! $template) {
            return false;
        }

        $to = $template->audience === 'admin' ? MailSettings::adminAddress() : $inquiry->email;

        return $this->send($template, $to, $this->baseValues() + [
            'guest_name' => $inquiry->name,
            'guest_email' => $inquiry->email,
            'guest_phone' => $inquiry->phone ?: '—',
            'guest_country' => $inquiry->country ?: '—',
            'inquiry_subject' => $inquiry->subject ?: 'your trip',
            'message' => $inquiry->message,
            'admin_link' => route('admin.inquiries.show', $inquiry),
        ]);
    }

    /**
     * Render a template without sending, for the editor preview and test emails.
     *
     * @return array{subject: string, html: string}
     */
    public function render(EmailTemplate $template, ?array $values = null): array
    {
        $values ??= $this->sampleValues();

        return [
            'subject' => $this->fill($template->subject, $values, false),
            'html' => $this->toHtml($template->body, $values),
        ];
    }

    public function sendTest(EmailTemplate $template, string $to): void
    {
        $rendered = $this->render($template);
        Mail::to($to)->send(new TemplateMail($template->key, '[Test] '.$rendered['subject'], $rendered['html']));
    }

    /** @return array<string, string> */
    public function sampleValues(): array
    {
        return $this->baseValues() + [
            'guest_name' => 'Harpreet Kaur',
            'guest_email' => 'harpreet@example.ca',
            'guest_phone' => '+1 416 555 0100',
            'guest_country' => 'Canada',
            'booking_reference' => 'SFR-AB12CD',
            'journey' => 'Footsteps of Guru Nanak: Sikh Yatra',
            'start_date' => now()->addMonths(2)->format('j F Y'),
            'travellers' => '2 adults, 1 child',
            'room' => 'Twin sharing',
            'options' => 'Package class: VIP · Hotel in Makkah: Fairmont Makkah Clock Royal Tower',
            'extras' => 'Pakistan SIM card with 30 GB data',
            'special_requests' => 'Vegetarian meals, please.',
            'total' => 'CA$8,350',
            'amount_paid' => 'CA$2,088',
            'balance' => 'CA$6,262',
            'status' => 'confirmed',
            'payment_amount' => 'CA$2,088',
            'payment_method' => 'Stripe',
            'booking_link' => url('/'),
            'payment_link' => url('/'),
            'admin_link' => url('/admin'),
            'inquiry_subject' => 'Sikh Yatra',
            'message' => 'We are a family of four hoping to travel for Vaisakhi.',
        ];
    }

    private function template(string $key): ?EmailTemplate
    {
        if (! MailSettings::enabled()) {
            return null;
        }

        $template = EmailTemplate::where('key', $key)->first();

        return $template?->is_enabled ? $template : null;
    }

    /** @param array<string, string> $values */
    private function send(EmailTemplate $template, ?string $to, array $values): bool
    {
        if (! $to || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        try {
            $rendered = $this->render($template, $values);
            Mail::to($to)->send(new TemplateMail($template->key, $rendered['subject'], $rendered['html']));

            return true;
        } catch (Throwable $e) {
            Log::warning("Email '{$template->key}' to {$to} failed: ".$e->getMessage());
            report($e);

            return false;
        }
    }

    /** @return array<string, string> */
    private function baseValues(): array
    {
        return [
            'site_name' => (string) setting('site_name', config('app.name')),
            'contact_phone' => (string) (setting('contact_phone_ca') ?: setting('contact_phone')),
            'contact_email' => (string) setting('contact_email'),
            'journeys_link' => route('packages.index'),
        ];
    }

    /** @return array<string, string> */
    private function bookingValues(Booking $booking, ?Payment $payment): array
    {
        $inGuestCurrency = fn ($base) => $booking->currency
            ? format_money((float) $base * $booking->exchange_rate, $booking->currency)
            : money_base($base);

        return $this->baseValues() + [
            'guest_name' => $booking->full_name,
            'guest_email' => $booking->email,
            'guest_phone' => $booking->phone ?: '—',
            'guest_country' => $booking->country ?: '—',
            'booking_reference' => $booking->reference,
            'journey' => $booking->package?->title ?? 'your journey',
            'start_date' => $booking->start_date?->format('j F Y') ?? 'to be confirmed',
            'travellers' => $booking->adults.' '.Str::plural('adult', $booking->adults).($booking->children ? ', '.$booking->children.' '.Str::plural('child', $booking->children) : ''),
            'room' => $booking->room_preference ?: 'Standard',
            'extras' => collect($booking->extras ?? [])->pluck('name')->join(', ') ?: 'None',
            'options' => collect($booking->options ?? [])->map(fn ($o) => $o['group'].': '.$o['label'])->join(' · ') ?: 'None',
            'special_requests' => $booking->special_requests ?: 'None',
            'total' => $inGuestCurrency($booking->total_estimate),
            'amount_paid' => $inGuestCurrency($booking->amount_paid),
            'balance' => $inGuestCurrency($booking->balanceDue()),
            'status' => $booking->status,
            'payment_amount' => $payment ? $payment->formattedAmount() : '',
            'payment_method' => $payment ? (app(PaymentGateways::class)->find($payment->gateway)?->name() ?? $payment->gateway) : '',
            'booking_link' => PaymentController::bookingLink($booking),
            'payment_link' => PaymentController::payLink($booking),
            'admin_link' => route('admin.bookings.show', $booking),
        ];
    }

    /** @param array<string, string> $values */
    private function fill(string $text, array $values, bool $escapeMarkdown): string
    {
        return preg_replace_callback('/\{([a-z_]+)\}/', function (array $match) use ($values, $escapeMarkdown) {
            if (! array_key_exists($match[1], $values)) {
                return $match[0];
            }
            $value = (string) $values[$match[1]];

            // Guest-typed values must not turn into Markdown formatting or links.
            return $escapeMarkdown && ! str_ends_with($match[1], '_link')
                ? preg_replace('/([\\\\`*_{}\[\]()#+!|>~<])/', '\\\\$1', $value)
                : $value;
        }, $text);
    }

    /** Markdown → HTML, with [[Label|url]] buttons and raw HTML escaped. */
    private function toHtml(string $body, array $values): string
    {
        $buttons = [];
        $body = preg_replace_callback('/\[\[([^|\]]+)\|([^\]]+)\]\]/', function (array $match) use (&$buttons, $values) {
            $token = 'BUTTONTOKEN'.count($buttons).'X';
            $buttons[$token] = [trim($this->fill($match[1], $values, false)), trim($this->fill($match[2], $values, false))];

            return $token;
        }, $body);

        $html = Str::markdown($this->fill($body, $values, true), ['html_input' => 'escape', 'allow_unsafe_links' => false]);

        foreach ($buttons as $token => [$label, $url]) {
            $safeUrl = preg_match('#^(https?://|mailto:)#i', $url) ? e($url) : '#';
            $button = '<table role="presentation" cellpadding="0" cellspacing="0" style="margin:8px 0"><tr><td class="button-cell" style="border-radius:10px">'
                .'<a href="'.$safeUrl.'" class="button" style="display:inline-block;padding:13px 22px;border-radius:10px;font-weight:600;text-decoration:none">'.e($label).'</a></td></tr></table>';
            $html = str_replace(['<p>'.$token.'</p>', $token], $button, $html);
        }

        return $html;
    }
}
