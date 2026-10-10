<?php

namespace App\Console\Commands;

use App\Support\ExchangeRates;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('currencies:update-rates')]
#[Description('Fetch the latest exchange rates for all currencies relative to the base currency')]
class UpdateCurrencyRates extends Command
{
    public function handle(ExchangeRates $rates): int
    {
        try {
            $count = $rates->update();
        } catch (Throwable $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Updated {$count} exchange rates (base: ".currencies()->base()->code.').');

        return self::SUCCESS;
    }
}
