<?php

use App\Models\Setting;
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

if (! function_exists('money')) {
    function money(float|int|string|null $amount, string $currency = 'USD'): string
    {
        $symbol = $currency === 'CAD' ? 'CA$' : '$';

        return $symbol.number_format((float) $amount, 0);
    }
}
