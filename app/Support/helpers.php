<?php

use App\Models\Setting;
use App\Support\CurrencyManager;
use Illuminate\Support\Facades\Storage;

if (! function_exists('media_url')) {
    /**
     * Resolve an image value that is either an absolute URL or a path on the public disk.
     */
    function media_url(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '/')) {
            return $value;
        }

        return Storage::disk('public')->url($value);
    }
}

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('currencies')) {
    function currencies(): CurrencyManager
    {
        return app(CurrencyManager::class);
    }
}

if (! function_exists('money')) {
    /** A base-currency amount converted to, and formatted in, the visitor's currency (or the given one). */
    function money(float|int|string|null $baseAmount, ?string $currency = null): string
    {
        $manager = currencies();

        return $manager->display($baseAmount, $currency ? $manager->find($currency) : null);
    }
}

if (! function_exists('money_base')) {
    /** A base-currency amount formatted in the base currency (used in the admin panel). */
    function money_base(float|int|string|null $amount): string
    {
        return currencies()->base()->format($amount, 0);
    }
}

if (! function_exists('format_money')) {
    /** An amount that is already in the given currency. */
    function format_money(float|int|string|null $amount, string $currency): string
    {
        $model = currencies()->find($currency);

        return $model ? $model->format($amount) : $currency.' '.number_format((float) $amount, 2);
    }
}
