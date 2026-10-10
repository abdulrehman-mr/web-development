<?php

namespace App\Support;

use App\Models\Currency;
use Illuminate\Support\Collection;

/**
 * Prices are stored in the base currency. Visitors can browse in any enabled
 * currency; amounts are converted with the stored exchange rates.
 */
class CurrencyManager
{
    public const SESSION_KEY = 'currency';

    private ?Collection $all = null;

    public function all(): Collection
    {
        return $this->all ??= Currency::cached();
    }

    /** Forget the per-request copy after currencies change. */
    public function flush(): void
    {
        $this->all = null;
    }

    public function find(?string $code): ?Currency
    {
        return $code ? $this->all()->get(strtoupper($code)) : null;
    }

    /** Enabled currencies that can actually be shown (they have a rate). */
    public function enabled(): Collection
    {
        return $this->all()->filter(fn (Currency $c) => $c->is_enabled && ($c->rate > 0 || $c->code === $this->base()->code));
    }

    public function base(): Currency
    {
        return $this->find(setting('base_currency', 'USD')) ?? $this->fallback();
    }

    /** The currency the current visitor is browsing in. */
    public function current(): Currency
    {
        $chosen = $this->find(session(self::SESSION_KEY));
        if ($chosen && $this->enabled()->has($chosen->code)) {
            return $chosen;
        }

        $default = $this->find(setting('default_currency'));

        return $default && $this->enabled()->has($default->code) ? $default : $this->base();
    }

    /** Optional second currency shown as "about CA$…" under prices. */
    public function secondary(): ?Currency
    {
        $secondary = $this->find(setting('secondary_currency'));

        return $secondary && $secondary->rate > 0 && $secondary->code !== $this->current()->code ? $secondary : null;
    }

    public function rate(Currency $currency): float
    {
        return $currency->code === $this->base()->code ? 1.0 : (float) ($currency->rate ?: 0);
    }

    /** Convert an amount in the base currency into another currency. */
    public function convert(float|int|string|null $baseAmount, Currency $to): float
    {
        return round((float) $baseAmount * $this->rate($to), $to->decimals);
    }

    /** Convert from the base currency and format for display. */
    public function display(float|int|string|null $baseAmount, ?Currency $in = null): string
    {
        $in ??= $this->current();
        if ($this->rate($in) <= 0) {
            $in = $this->base();
        }

        // Tour prices read best as whole numbers in every currency.
        return $in->format($this->convert($baseAmount, $in), 0);
    }

    /** Re-express every rate relative to a new base currency. */
    public function rebase(string $newBase): void
    {
        $new = $this->find($newBase);
        $factor = $new && $new->code !== $this->base()->code ? $this->rate($new) : 1.0;
        if ($factor <= 0) {
            return;
        }

        foreach (Currency::whereNotNull('rate')->get() as $currency) {
            $currency->update(['rate' => $currency->code === $newBase ? 1 : $currency->rate / $factor]);
        }
        Currency::where('code', $this->base()->code)->update(['rate' => 1 / $factor]);
        Currency::where('code', $newBase)->update(['rate' => 1]);
        Currency::forgetCache();
    }

    private function fallback(): Currency
    {
        return new Currency(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'symbol_position' => 'before', 'decimals' => 2, 'rate' => 1, 'is_enabled' => true]);
    }
}
