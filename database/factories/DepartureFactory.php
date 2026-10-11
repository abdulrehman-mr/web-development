<?php

namespace Database\Factories;

use App\Models\Departure;
use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Departure>
 */
class DepartureFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Packages have no factory; tests run against the seeded journeys.
            'package_id' => fn () => Package::firstOrFail()->id,
            'starts_on' => now()->addMonths(2)->startOfDay(),
            'seats' => 12,
            'price_adjustment' => 0,
            'label' => null,
            'is_closed' => false,
        ];
    }

    public function closed(): static
    {
        return $this->state(['is_closed' => true]);
    }
}
