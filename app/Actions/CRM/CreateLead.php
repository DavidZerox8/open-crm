<?php

namespace App\Actions\CRM;

use App\Enums\LeadStatus;
use App\Models\CRM\Lead;
use App\Models\User;

class CreateLead
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(User $user, array $attributes): Lead
    {
        return Lead::create([
            'account_id' => $user->current_account_id,
            'owner_id' => $attributes['owner_id'] ?? $user->id,
            'company_name' => $attributes['company_name'] ?? null,
            'contact_name' => $attributes['contact_name'],
            'email' => $attributes['email'] ?? null,
            'phone' => $attributes['phone'] ?? null,
            'source' => $attributes['source'] ?? null,
            'status' => $attributes['status'] ?? LeadStatus::New,
            'score' => $attributes['score'] ?? 0,
            'notes' => $attributes['notes'] ?? null,
        ]);
    }
}
