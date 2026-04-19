<?php

namespace App\Policies;

use App\Models\CRM\Pipeline;
use App\Models\User;

class PipelinePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('pipeline.view');
    }

    public function view(User $user, Pipeline $pipeline): bool
    {
        return $user->can('pipeline.view') && $user->current_account_id === $pipeline->account_id;
    }

    public function manage(User $user): bool
    {
        return $user->can('pipeline.manage');
    }

    public function update(User $user, Pipeline $pipeline): bool
    {
        return $user->can('pipeline.manage') && $user->current_account_id === $pipeline->account_id;
    }

    public function delete(User $user, Pipeline $pipeline): bool
    {
        return $user->can('pipeline.manage') && $user->current_account_id === $pipeline->account_id;
    }
}
