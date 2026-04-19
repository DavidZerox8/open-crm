<?php

namespace Database\Factories\CRM;

use App\Enums\LeadStatus;
use App\Models\Account;
use App\Models\CRM\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'owner_id' => null,
            'company_name' => fake()->optional()->company(),
            'contact_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'source' => fake()->randomElement(['web', 'referral', 'campaign', 'event', 'outbound', 'inbound']),
            'status' => fake()->randomElement(LeadStatus::cases()),
            'score' => fake()->numberBetween(0, 100),
            'notes' => fake()->optional()->sentence(12),
            'converted_at' => null,
            'converted_company_id' => null,
            'converted_contact_id' => null,
            'converted_deal_id' => null,
        ];
    }

    public function statusNew(): self
    {
        return $this->state(['status' => LeadStatus::New]);
    }

    public function qualified(): self
    {
        return $this->state(['status' => LeadStatus::Qualified]);
    }
}
