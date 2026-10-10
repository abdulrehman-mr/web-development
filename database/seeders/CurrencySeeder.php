<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Approximate starting rates (per 1 USD) for the currencies enabled out of the box.
     * Use Admin → Currencies → "Update rates" (or `php artisan currencies:update-rates`) for live rates.
     *
     * @var array<string, float>
     */
    private const STARTER_RATES = [
        'USD' => 1, 'CAD' => 1.38, 'GBP' => 0.75, 'EUR' => 0.86, 'AUD' => 1.53, 'PKR' => 282, 'AED' => 3.6725,
    ];

    public function run(): void
    {
        foreach (config('currencies') as $code => [$name, $symbol, $decimals]) {
            Currency::updateOrCreate(['code' => $code], [
                'name' => $name,
                'symbol' => $symbol,
                'decimals' => $decimals,
                'symbol_position' => 'before',
                'rate' => self::STARTER_RATES[$code] ?? null,
                'is_enabled' => isset(self::STARTER_RATES[$code]),
            ]);
        }
    }
}
