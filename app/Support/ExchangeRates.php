<?php

namespace App\Support;

use App\Models\Currency;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Pulls daily exchange rates from ExchangeRate-API's free open endpoint
 * (https://www.exchangerate-api.com/docs/free — no key needed, attribution requested).
 */
class ExchangeRates
{
    public const ENDPOINT = 'https://open.er-api.com/v6/latest/';

    /** Update every currency's rate relative to the base currency. Returns how many were updated. */
    public function update(): int
    {
        $base = currencies()->base()->code;

        $response = Http::connectTimeout(5)
            ->timeout(15)
            ->retry([200, 1000], 0, fn (Throwable $e) => $e instanceof ConnectionException
                || ($e instanceof RequestException && $e->response->serverError()), throw: false)
            ->get(self::ENDPOINT.$base);

        if (! $response->successful() || $response->json('result') !== 'success') {
            throw new RuntimeException('Rate provider error: '.($response->json('error-type') ?? 'HTTP '.$response->status()));
        }

        $rates = $response->json('rates', []);
        $updated = 0;
        foreach (Currency::all() as $currency) {
            if (isset($rates[$currency->code]) && (float) $rates[$currency->code] > 0) {
                $currency->rate = $currency->code === $base ? 1 : (float) $rates[$currency->code];
                $currency->rate_updated_at = now();
                $currency->saveQuietly();
                $updated++;
            }
        }
        Currency::forgetCache();

        return $updated;
    }
}
