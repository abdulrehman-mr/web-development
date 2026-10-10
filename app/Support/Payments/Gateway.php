<?php

namespace App\Support\Payments;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A payment provider. Every gateway uses the provider's hosted checkout, so
 * card details never touch this server, and confirms payments server-side
 * (API lookup or signed webhook) rather than trusting the browser redirect.
 */
abstract class Gateway
{
    abstract public function key(): string;

    abstract public function name(): string;

    abstract public function description(): string;

    /**
     * Admin settings for this gateway.
     *
     * @return array<string, array{label: string, type: string, help?: string, options?: array<string, string>, required?: bool}>
     */
    abstract public function fields(): array;

    /** Send the guest to the provider's checkout. */
    abstract public function start(Payment $payment): Response;

    /** Called when the guest comes back from the provider. Should mark the payment paid if it can verify it. */
    abstract public function handleReturn(Request $request, Payment $payment): void;

    /** Server-to-server notification from the provider. */
    abstract public function handleWebhook(Request $request): Response;

    public function config(string $field): ?string
    {
        $key = "payment_{$this->key()}_{$field}";

        return ($this->fields()[$field]['type'] ?? null) === 'secret' ? Setting::secret($key) : setting($key);
    }

    public function isConfigured(): bool
    {
        foreach ($this->fields() as $field => $definition) {
            if (($definition['required'] ?? false) && blank($this->config($field))) {
                return false;
            }
        }

        return true;
    }

    public function isEnabled(): bool
    {
        return setting("payment_{$this->key()}_enabled") === '1' && $this->isConfigured();
    }

    public function sandbox(): bool
    {
        return $this->config('mode') !== 'live';
    }

    /** Null means "any ISO currency". */
    public function supportedCurrencies(): ?array
    {
        return null;
    }

    /** Charge in the guest's currency when the gateway supports it, otherwise in the site's base currency. */
    public function chargeCurrency(Booking $booking): string
    {
        $supported = $this->supportedCurrencies();
        $preferred = [$booking->currency, currencies()->base()->code, 'USD'];

        foreach (array_filter($preferred) as $code) {
            if ($supported === null || in_array($code, $supported, true)) {
                return $code;
            }
        }

        return $supported[0];
    }

    public function webhookUrl(): string
    {
        return route('payments.webhook', $this->key());
    }

    protected function itemName(Payment $payment): string
    {
        $title = $payment->booking->package?->title ?? 'Journey';
        $label = $payment->type === 'deposit' ? 'Deposit' : 'Payment';

        return mb_substr("{$label}: {$title} ({$payment->booking->reference})", 0, 120);
    }

    /** Amount as a decimal string in the currency's minor-unit precision, e.g. "612.50" or "6125". */
    protected function decimalAmount(Payment $payment, ?int $decimals = null): string
    {
        $decimals ??= currencies()->find($payment->currency)?->decimals ?? 2;

        return number_format((float) $payment->amount, $decimals, '.', '');
    }

    protected function returnUrl(Payment $payment): string
    {
        return route('payments.return', $payment);
    }

    protected function cancelUrl(Payment $payment): string
    {
        return route('payments.cancel', $payment);
    }

    /** Auto-submitting form for providers that start checkout with a POST. */
    protected function postRedirect(string $url, array $fields): Response
    {
        return response()->view('payments.redirect', ['url' => $url, 'fields' => $fields, 'gateway' => $this->name()]);
    }
}
