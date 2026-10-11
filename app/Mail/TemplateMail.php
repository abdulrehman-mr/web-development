<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** An email built from an editable template (Admin → Email templates). */
class TemplateMail extends Mailable
{
    use Queueable;

    public function __construct(
        public string $templateKey,
        public string $renderedSubject,
        public string $renderedHtml,
        public string $preheader = '',
    ) {}

    public function envelope(): Envelope
    {
        $replyTo = setting('mail_reply_to') ?: setting('contact_email');

        return new Envelope(
            subject: $this->renderedSubject,
            replyTo: $replyTo ? [new Address($replyTo)] : [],
            tags: [$this->templateKey],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.layout', with: ['html' => $this->renderedHtml, 'preheader' => $this->preheader]);
    }
}
