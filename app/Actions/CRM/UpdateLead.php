<?php

namespace App\Actions\CRM;

use App\Models\CRM\Lead;

class UpdateLead
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Lead $lead, array $attributes): Lead
    {
        $lead->fill(array_filter(
            [
                'owner_id' => $attributes['owner_id'] ?? null,
                'company_name' => $attributes['company_name'] ?? null,
                'contact_name' => $attributes['contact_name'] ?? null,
                'email' => $attributes['email'] ?? null,
                'phone' => $attributes['phone'] ?? null,
                'source' => $attributes['source'] ?? null,
                'status' => $attributes['status'] ?? null,
                'score' => $attributes['score'] ?? null,
                'notes' => $attributes['notes'] ?? null,
            ],
            fn ($value) => $value !== null,
        ));

        $lead->save();

        return $lead;
    }
}
