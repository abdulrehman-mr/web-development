<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PageSection>
 */
class PageSectionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'type' => 'text',
            'data' => ['title' => fake()->sentence(4), 'body' => fake()->paragraph()],
            'is_enabled' => true,
            'sort_order' => 0,
        ];
    }

    /** @param  array<string, mixed>  $data */
    public function hero(array $data = []): static
    {
        return $this->state(['type' => 'hero', 'data' => $data + ['title' => fake()->sentence(4)]]);
    }

    public function disabled(): static
    {
        return $this->state(['is_enabled' => false]);
    }
}
