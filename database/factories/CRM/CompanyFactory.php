<?php

namespace Database\Factories\CRM;

use App\Models\Account;
use App\Models\CRM\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'owner_id' => null,
            'name' => fake()->unique()->company(),
            'legal_name' => fake()->optional()->companySuffix(),
            'industry' => fake()->randomElement(['SaaS', 'Retail', 'Fintech', 'Healthtech', 'Agencia', 'Manufactura', 'Educacion']),
            'website' => fake()->optional()->url(),
            'phone' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->companyEmail(),
            'address' => fake()->optional()->streetAddress(),
            'city' => fake()->optional()->city(),
            'country' => fake()->optional()->countryCode(),
            'notes' => fake()->optional()->sentence(12),
        ];
    }
}
