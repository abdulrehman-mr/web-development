<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Default wording for every automatic email. Bodies are Markdown;
     * {placeholders} are filled in when sending and [[Label|url]] becomes a button.
     *
     * @var array<string, array{name: string, description: string, audience: string, subject: string, body: string}>
     */
    public const DEFAULTS = [
        'booking_received_guest' => [
            'name' => 'Booking request received',
            'description' => 'Sent to the guest right after they request a journey.',
            'audience' => 'guest',
            'subject' => 'We’ve received your request — {journey}',
            'body' => "Hello {guest_name},\n\nThank you for choosing {site_name}! We’ve received your request for **{journey}** starting **{start_date}** for {travellers}.\n\n**Your reference:** {booking_reference}  \n**Estimated total:** {total}\n\nA travel designer will check availability and reply within one working day with your tailored itinerary.\n\n[[View your booking|{booking_link}]]\n\nQuestions? Reply to this email or call us on {contact_phone}.\n\nWarm regards,  \nThe {site_name} team",
        ],
        'booking_received_admin' => [
            'name' => 'New booking (to your team)',
            'description' => 'Sent to your notification address when a guest requests a journey.',
            'audience' => 'admin',
            'subject' => 'New booking {booking_reference}: {journey}',
            'body' => "**{guest_name}** ({guest_email}, {guest_country}) requested **{journey}**.\n\n- Start date: {start_date}\n- Travellers: {travellers}\n- Room: {room}\n- Extras: {extras}\n- Estimated total: {total}\n\nSpecial requests:\n\n> {special_requests}\n\n[[Open in admin|{admin_link}]]",
        ],
        'booking_confirmed_guest' => [
            'name' => 'Booking confirmed',
            'description' => 'Sent to the guest when you change a booking’s status to Confirmed.',
            'audience' => 'guest',
            'subject' => 'Your journey is confirmed — {journey}',
            'body' => "Hello {guest_name},\n\nGreat news: your **{journey}** starting **{start_date}** is confirmed!\n\n**Reference:** {booking_reference}  \n**Total:** {total}  \n**Paid so far:** {amount_paid}\n\nTo secure your dates, please pay your deposit using the secure link below. Once it’s received we’ll send your e-visa invitation letter.\n\n[[Pay securely|{payment_link}]]\n\nWe can’t wait to welcome you to Pakistan.\n\nThe {site_name} team",
        ],
        'booking_cancelled_guest' => [
            'name' => 'Booking cancelled',
            'description' => 'Sent to the guest when you change a booking’s status to Cancelled.',
            'audience' => 'guest',
            'subject' => 'Your booking {booking_reference} has been cancelled',
            'body' => "Hello {guest_name},\n\nYour booking **{booking_reference}** for **{journey}** has been cancelled.\n\nIf this wasn’t expected, or you’d like to choose new dates, just reply to this email or call {contact_phone}.\n\nThe {site_name} team",
        ],
        'payment_link_guest' => [
            'name' => 'Payment link',
            'description' => 'Sent when you click “Email payment link” on a booking.',
            'audience' => 'guest',
            'subject' => 'Payment for your journey — {booking_reference}',
            'body' => "Hello {guest_name},\n\nYou can pay for **{journey}** securely online. Choose a deposit or the full balance and your preferred payment method.\n\n**Balance due:** {balance}\n\n[[Pay securely|{payment_link}]]\n\nThis link is valid for 30 days.\n\nThe {site_name} team",
        ],
        'payment_received_guest' => [
            'name' => 'Payment received',
            'description' => 'Sent to the guest when an online payment is confirmed.',
            'audience' => 'guest',
            'subject' => 'Payment received — thank you!',
            'body' => "Hello {guest_name},\n\nWe’ve received your payment of **{payment_amount}** via {payment_method} for **{journey}**.\n\n**Paid so far:** {amount_paid}  \n**Balance remaining:** {balance}\n\n[[View your booking|{booking_link}]]\n\nThank you,  \nThe {site_name} team",
        ],
        'payment_received_admin' => [
            'name' => 'Payment received (to your team)',
            'description' => 'Sent to your notification address when an online payment is confirmed.',
            'audience' => 'admin',
            'subject' => 'Payment {payment_amount} received for {booking_reference}',
            'body' => "{guest_name} paid **{payment_amount}** via {payment_method} for **{journey}**.\n\nPaid so far: {amount_paid} · Balance: {balance}\n\n[[Open in admin|{admin_link}]]",
        ],
        'inquiry_received_admin' => [
            'name' => 'New enquiry (to your team)',
            'description' => 'Sent to your notification address when someone uses the contact form.',
            'audience' => 'admin',
            'subject' => 'New enquiry from {guest_name}: {inquiry_subject}',
            'body' => "**{guest_name}** ({guest_email}, {guest_phone}, {guest_country}) wrote:\n\n> {message}\n\n[[Open in admin|{admin_link}]]",
        ],
        'inquiry_autoreply_guest' => [
            'name' => 'Enquiry auto-reply',
            'description' => 'Sent to the person who used the contact form.',
            'audience' => 'guest',
            'subject' => 'Thanks for getting in touch with {site_name}',
            'body' => "Hello {guest_name},\n\nThank you for your message about **{inquiry_subject}**. A travel designer will reply within one working day.\n\nIn the meantime, you can browse our journeys:\n\n[[See all journeys|{journeys_link}]]\n\nThe {site_name} team",
        ],
    ];

    /** Adds any missing templates; never overwrites edited ones. */
    public function run(): void
    {
        foreach (self::DEFAULTS as $key => $template) {
            EmailTemplate::firstOrCreate(['key' => $key], $template);
        }
    }
}
