<?php

namespace App\Policies;

use App\Models\CRM\Activity;
use App\Models\User;

class ActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('activities.view');
    }

    public function view(User $user, Activity $activity): bool
    {
        return $user->can('activities.view') && $user->current_account_id === $activity->account_id;
    }

    public function create(User $user): bool
    {
        return $user->can('activities.create');
    }

    public function update(User $user, Activity $activity): bool
    {
        return $user->can('activities.update')
            && $user->current_account_id === $activity->account_id
            && $activity->user_id === $user->id;
    }

    public function delete(User $user, Activity $activity): bool
    {
        return $user->can('activities.delete')
            && $user->current_account_id === $activity->account_id
            && $activity->user_id === $user->id;
    }
}
