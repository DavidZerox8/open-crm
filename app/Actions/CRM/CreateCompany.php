<?php

namespace App\Actions\CRM;

use App\Models\CRM\Company;
use App\Models\User;

class CreateCompany
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(User $user, array $attributes): Company
    {
        return Company::create([
            'account_id' => $user->current_account_id,
            'owner_id' => $attributes['owner_id'] ?? $user->id,
            'name' => $attributes['name'],
            'legal_name' => $attributes['legal_name'] ?? null,
            'industry' => $attributes['industry'] ?? null,
            'website' => $attributes['website'] ?? null,
            'phone' => $attributes['phone'] ?? null,
            'email' => $attributes['email'] ?? null,
            'address' => $attributes['address'] ?? null,
            'city' => $attributes['city'] ?? null,
            'country' => $attributes['country'] ?? null,
            'notes' => $attributes['notes'] ?? null,
        ]);
    }
}
