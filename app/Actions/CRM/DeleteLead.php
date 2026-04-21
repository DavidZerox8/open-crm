<?php

namespace App\Actions\CRM;

use App\Models\CRM\Lead;

class DeleteLead
{
    public function execute(Lead $lead): bool
    {
        return $lead->delete();
    }
}
