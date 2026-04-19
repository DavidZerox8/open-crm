<?php

namespace Database\Factories\CRM;

use App\Models\Account;
use App\Models\CRM\Pipeline;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Pipeline>
 */
class PipelineFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Ventas', 'Onboarding', 'Renovaciones', 'Partner']);

        return [
            'account_id' => Account::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'is_default' => false,
            'position' => fake()->numberBetween(0, 10),
        ];
    }

    public function default(): self
    {
        return $this->state(['is_default' => true, 'position' => 0]);
    }
}
