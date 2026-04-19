<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::headline(fake()->unique()->word());

        return [
            'account_id' => Account::factory(),
            'name' => $name,
            'guard_name' => 'web',
        ];
    }
}
