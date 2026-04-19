<?php

namespace Database\Factories\CRM;

use App\Enums\ActivityType;
use App\Models\Account;
use App\Models\CRM\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'user_id' => null,
            'subject_type' => null,
            'subject_id' => null,
            'type' => fake()->randomElement(ActivityType::cases()),
            'title' => fake()->sentence(4),
            'body' => fake()->optional()->paragraph(),
            'occurred_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
