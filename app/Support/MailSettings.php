<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\NativeTransportFactory;

/**
 * Applies the email settings from Admin → Email to Laravel's mailer at runtime.
 * Email never reads MAIL_* environment variables: until something is saved in the
 * admin, it sends with PHP mail from the site's contact email.
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
        $driver = setting('mail_driver', 'php');
        if (! array_key_exists((string) $driver, self::DRIVERS)) {
            $driver = 'php';
        }

        config([
            'mail.default' => $driver,
            'mail.mailers.php' => ['transport' => 'native'],
            'mail.mailers.smtp.scheme' => setting('mail_encryption') === 'ssl' ? 'smtps' : 'smtp',
            'mail.mailers.smtp.host' => setting('mail_host'),
            'mail.mailers.smtp.port' => (int) setting('mail_port', 587),
            'mail.mailers.smtp.username' => setting('mail_username'),
            'mail.mailers.smtp.password' => Setting::secret('mail_password'),
            'mail.from.address' => setting('mail_from_address') ?: setting('contact_email') ?: 'noreply@'.(parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost'),
            'mail.from.name' => setting('mail_from_name') ?: setting('site_name') ?: config('app.name'),
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
