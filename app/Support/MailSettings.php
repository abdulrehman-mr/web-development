<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\NativeTransportFactory;

/**
 * Applies the email settings from Admin → Email to Laravel's mailer at runtime.
 * Until a sending method is chosen in the admin, the .env MAIL_* values apply.
 */
class MailSettings
{
    /** @var array<string, string> */
    public const DRIVERS = [
        'php' => 'PHP mail (the server’s built-in mailer)',
        'smtp' => 'SMTP server (Gmail, Outlook, your host, SendGrid, Mailgun…)',
        'log' => 'Don’t send — write emails to the log (testing)',
    ];

    public static function register(): void
    {
        // "PHP mail": Symfony's native transport sends through the sendmail_path configured in php.ini,
        // which is what PHP's mail() uses.
        Mail::extend('native', fn () => (new NativeTransportFactory)->create(new Dsn('native', 'default')));
    }

    public static function apply(): void
    {
        $driver = setting('mail_driver');
        if (! array_key_exists((string) $driver, self::DRIVERS)) {
            return;
        }

        config([
            'mail.default' => $driver,
            'mail.mailers.php' => ['transport' => 'native'],
            'mail.mailers.smtp.scheme' => setting('mail_encryption') === 'ssl' ? 'smtps' : 'smtp',
            'mail.mailers.smtp.host' => setting('mail_host'),
            'mail.mailers.smtp.port' => (int) setting('mail_port', 587),
            'mail.mailers.smtp.username' => setting('mail_username'),
            'mail.mailers.smtp.password' => Setting::secret('mail_password'),
            'mail.from.address' => setting('mail_from_address', config('mail.from.address')),
            'mail.from.name' => setting('mail_from_name', setting('site_name', config('mail.from.name'))),
        ]);

        Mail::purge($driver);
    }

    public static function enabled(): bool
    {
        return setting('mail_enabled', '1') === '1';
    }

    /** Where team notifications go: the notification address, else the contact email. */
    public static function adminAddress(): ?string
    {
        return setting('mail_admin_address') ?: setting('contact_email');
    }
}
