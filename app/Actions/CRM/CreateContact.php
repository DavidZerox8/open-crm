<?php

namespace App\Actions\CRM;

use App\Models\CRM\Contact;
use App\Models\User;

class CreateContact
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(User $user, array $attributes): Contact
    {
        return Contact::create([
            'account_id' => $user->current_account_id,
            'company_id' => $attributes['company_id'] ?? null,
            'owner_id' => $attributes['owner_id'] ?? $user->id,
            'first_name' => $attributes['first_name'],
            'last_name' => $attributes['last_name'] ?? null,
            'job_title' => $attributes['job_title'] ?? null,
            'email' => $attributes['email'] ?? null,
            'phone' => $attributes['phone'] ?? null,
            'mobile' => $attributes['mobile'] ?? null,
            'notes' => $attributes['notes'] ?? null,
        ]);
    }
}
