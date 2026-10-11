<?php

namespace Tests\Feature;

use App\Mail\TemplateMail;
use App\Models\Booking;
use App\Models\EmailTemplate;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use App\Support\MailSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Tests\TestCase;

class EmailTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Setting::put(['mail_admin_address' => 'team@safarnama.test']);
    }

    private function requestBooking(array $overrides = []): Booking
    {
        $package = Package::firstOrFail();
        $this->post("/journeys/{$package->slug}/book", $overrides + [
            'full_name' => 'Harpreet Kaur', 'email' => 'harpreet@example.ca', 'adults' => 2,
            'start_date' => now()->addMonth()->toDateString(),
        ])->assertSessionHasNoErrors();

        return Booking::latest('id')->firstOrFail();
    }

    private function sentKeys(): array
    {
        return Mail::sent(TemplateMail::class)->map(fn (TemplateMail $m) => $m->templateKey.'→'.$m->to[0]['address'])->all();
    }

    public function test_booking_emails_the_guest_and_the_team(): void
    {
        $booking = $this->requestBooking();

        $this->assertSame(['booking_received_guest→harpreet@example.ca', 'booking_received_admin→team@safarnama.test'], $this->sentKeys());
        Mail::assertSent(TemplateMail::class, fn (TemplateMail $m) => $m->templateKey === 'booking_received_guest'
            && str_contains($m->renderedSubject, $booking->package->title)
            && str_contains($m->renderedHtml, $booking->reference)
            && str_contains($m->renderedHtml, 'class="button"'));
    }

    public function test_disabled_template_and_master_switch_stop_emails(): void
    {
        EmailTemplate::where('key', 'booking_received_admin')->update(['is_enabled' => false]);
        $this->requestBooking();
        $this->assertSame(['booking_received_guest→harpreet@example.ca'], $this->sentKeys());

        Setting::put(['mail_enabled' => '0']);
        $this->requestBooking(['email' => 'second@example.ca']);
        Mail::assertSentCount(1);
    }

    public function test_guest_input_cannot_inject_html(): void
    {
        $this->requestBooking(['full_name' => '<img src=x onerror=alert(1)> **Bold**', 'special_requests' => '[click](javascript:alert(1))']);

        Mail::assertSent(TemplateMail::class, fn (TemplateMail $m) => $m->templateKey === 'booking_received_admin'
            && ! str_contains($m->renderedHtml, '<img')
            && ! str_contains($m->renderedHtml, 'href="javascript:')
            && str_contains($m->renderedHtml, '&lt;img')
            && ! str_contains($m->renderedHtml, '<strong>Bold</strong>'));
    }

    public function test_status_changes_email_the_guest(): void
    {
        $booking = $this->requestBooking();
        $this->actingAs(User::first());

        $this->put("/admin/bookings/{$booking->reference}", ['status' => 'confirmed'])->assertSessionHas('status', 'Booking updated. The guest has been emailed.');
        $this->put("/admin/bookings/{$booking->reference}", ['status' => 'confirmed', 'admin_notes' => 'x']);
        $this->put("/admin/bookings/{$booking->reference}", ['status' => 'cancelled']);

        $keys = array_values(array_filter($this->sentKeys(), fn ($k) => str_starts_with($k, 'booking_c')));
        $this->assertSame(['booking_confirmed_guest→harpreet@example.ca', 'booking_cancelled_guest→harpreet@example.ca'], $keys);
    }

    public function test_payment_emails_are_sent_once(): void
    {
        $booking = $this->requestBooking();
        $payment = $booking->payments()->create(['gateway' => 'stripe', 'type' => 'deposit', 'amount' => 500, 'currency' => 'USD', 'base_amount' => 500]);

        $payment->markPaid('pi_1');
        Payment::find($payment->id)->markPaid('pi_1');

        $keys = array_values(array_filter($this->sentKeys(), fn ($k) => str_starts_with($k, 'payment_')));
        $this->assertSame(['payment_received_guest→harpreet@example.ca', 'payment_received_admin→team@safarnama.test'], $keys);
    }

    public function test_admin_can_email_a_payment_link(): void
    {
        $booking = $this->requestBooking();
        $this->actingAs(User::first());

        $this->post("/admin/bookings/{$booking->reference}/payment-link")->assertSessionHas('status');

        Mail::assertSent(TemplateMail::class, fn (TemplateMail $m) => $m->templateKey === 'payment_link_guest' && str_contains($m->renderedHtml, 'signature='));
    }

    public function test_contact_form_notifies_team_and_auto_replies(): void
    {
        $this->post('/contact', ['name' => 'Daniel', 'email' => 'daniel@example.ca', 'message' => 'Hunza in May?', 'subject' => 'Northern Highlands']);

        $this->assertSame(['inquiry_received_admin→team@safarnama.test', 'inquiry_autoreply_guest→daniel@example.ca'], $this->sentKeys());
    }

    public function test_admin_edits_previews_tests_and_resets_a_template(): void
    {
        $this->actingAs(User::first());
        $template = EmailTemplate::where('key', 'booking_received_guest')->firstOrFail();

        $this->get('/admin/email-templates')->assertOk()->assertSee('Booking request received');
        $this->get("/admin/email-templates/{$template->id}/edit")->assertOk()->assertSee('{payment_link}');

        $this->post("/admin/email-templates/{$template->id}/preview", ['subject' => 'Hi {guest_name}', 'body' => 'Trip: **{journey}**'])
            ->assertOk()->assertSee('<strong>Footsteps of Guru Nanak: Sikh Yatra</strong>', false)
            ->assertHeader('X-Email-Subject', rawurlencode('Hi Harpreet Kaur'));

        $this->put("/admin/email-templates/{$template->id}", ['subject' => 'Custom {booking_reference}', 'body' => 'Custom body', 'is_enabled' => '1']);
        $this->assertSame('Custom {booking_reference}', $template->fresh()->subject);

        $this->post("/admin/email-templates/{$template->id}/test", ['test_to' => 'me@example.com'])->assertSessionHas('status');
        Mail::assertSent(TemplateMail::class, fn (TemplateMail $m) => $m->renderedSubject === '[Test] Custom SFR-AB12CD');

        $this->patch("/admin/email-templates/{$template->id}/toggle");
        $this->assertFalse($template->fresh()->is_enabled);

        $this->post("/admin/email-templates/{$template->id}/reset");
        $this->assertStringContainsString('We’ve received your request', $template->fresh()->subject);
    }

    public function test_smtp_settings_are_applied_with_an_encrypted_password(): void
    {
        $this->actingAs(User::first());

        $this->put('/admin/email', [
            'mail_enabled' => '1', 'mail_driver' => 'smtp', 'mail_host' => 'smtp.gmail.com', 'mail_port' => 465, 'mail_encryption' => 'ssl',
            'mail_username' => 'me@gmail.com', 'mail_password' => 'app-password', 'mail_from_address' => 'hello@safarnama.travel', 'mail_from_name' => 'Safarnama',
        ])->assertSessionHasNoErrors();

        $this->assertNotSame('app-password', Setting::get('mail_password'));
        MailSettings::apply();
        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtps', config('mail.mailers.smtp.scheme'));
        $this->assertSame('smtp.gmail.com', config('mail.mailers.smtp.host'));
        $this->assertSame('app-password', config('mail.mailers.smtp.password'));
        $this->assertSame('hello@safarnama.travel', config('mail.from.address'));
        $this->get('/admin/email')->assertOk()->assertDontSee('app-password');
    }

    public function test_php_mail_option_uses_the_native_sendmail_transport(): void
    {
        Setting::put(['mail_driver' => 'php']);
        MailSettings::apply();

        $this->assertSame('php', config('mail.default'));
        $this->assertInstanceOf(SendmailTransport::class, app('mail.manager')->mailer('php')->getSymfonyTransport());
    }

    public function test_smtp_requires_a_host(): void
    {
        $this->actingAs(User::first());

        $this->put('/admin/email', ['mail_driver' => 'smtp', 'mail_from_address' => 'a@b.co', 'mail_from_name' => 'X'])
            ->assertSessionHasErrors(['mail_host', 'mail_port']);
    }

    public function test_email_uses_admin_settings_never_mail_env_variables(): void
    {
        Setting::whereIn('key', ['mail_driver', 'mail_from_address', 'mail_from_name'])->delete();
        Setting::put(['contact_email' => 'hello@safarnama.travel']);
        putenv('MAIL_MAILER=smtp');
        putenv('MAIL_FROM_ADDRESS=env@example.com');

        MailSettings::apply();

        $this->assertSame('php', config('mail.default'));
        $this->assertSame('hello@safarnama.travel', config('mail.from.address'));
        $this->assertSame('Safarnama', config('mail.from.name'));
        putenv('MAIL_MAILER');
        putenv('MAIL_FROM_ADDRESS');
    }
}
