<?php

namespace Database\Factories\CRM;

use App\Models\Account;
use App\Models\CRM\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'company_id' => null,
            'owner_id' => null,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'job_title' => fake()->optional()->jobTitle(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'mobile' => fake()->optional()->phoneNumber(),
            'notes' => fake()->optional()->sentence(10),
        ];
    }
}
