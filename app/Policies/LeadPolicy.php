<?php

namespace App\Policies;

use App\Models\CRM\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('leads.view');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->can('leads.view') && $user->current_account_id === $lead->account_id;
    }

    public function create(User $user): bool
    {
        return $user->can('leads.create');
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->can('leads.update') && $user->current_account_id === $lead->account_id;
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->can('leads.delete') && $user->current_account_id === $lead->account_id;
    }

    public function convert(User $user, Lead $lead): bool
    {
        return $user->can('leads.convert')
            && $user->current_account_id === $lead->account_id
            && $lead->converted_at === null;
    }
}
