<?php

namespace App\Support\Payments;

use Illuminate\Support\Collection;

class PaymentGateways
{
    /** @var array<int, class-string<Gateway>> */
    public const DRIVERS = [
        StripeGateway::class,
        PayPalGateway::class,
        AuthorizeNetGateway::class,
        TwoCheckoutGateway::class,
        CoinPaymentsGateway::class,
    ];

    /** @return Collection<string, Gateway> */
    public function all(): Collection
    {
        return collect(self::DRIVERS)->map(fn ($class) => app($class))->keyBy(fn (Gateway $g) => $g->key());
    }

    /** @return Collection<string, Gateway> */
    public function enabled(): Collection
    {
        return $this->all()->filter(fn (Gateway $g) => $g->isEnabled());
    }

    public function find(string $key): ?Gateway
    {
        return $this->all()->get($key);
    }
}
