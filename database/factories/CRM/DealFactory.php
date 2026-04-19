<?php

namespace Database\Factories\CRM;

use App\Enums\DealStatus;
use App\Models\Account;
use App\Models\CRM\Deal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deal>
 */
class DealFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'pipeline_id' => null,
            'stage_id' => null,
            'company_id' => null,
            'contact_id' => null,
            'owner_id' => null,
            'title' => fake()->catchPhrase(),
            'amount' => fake()->randomFloat(2, 500, 150000),
            'currency' => 'EUR',
            'probability' => fake()->numberBetween(10, 90),
            'expected_close_date' => fake()->optional()->dateTimeBetween('now', '+90 days')?->format('Y-m-d'),
            'closed_at' => null,
            'status' => DealStatus::Open,
            'lost_reason' => null,
        ];
    }

    public function won(): self
    {
        return $this->state(fn () => [
            'status' => DealStatus::Won,
            'probability' => 100,
            'closed_at' => now(),
        ]);
    }

    public function lost(): self
    {
        return $this->state(fn () => [
            'status' => DealStatus::Lost,
            'probability' => 0,
            'closed_at' => now(),
            'lost_reason' => fake()->randomElement(['price', 'competitor', 'timing', 'no_fit']),
        ]);
    }
}
