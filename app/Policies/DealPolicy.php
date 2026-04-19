<?php

namespace App\Policies;

use App\Models\CRM\Deal;
use App\Models\User;

class DealPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('deals.view');
    }

    public function view(User $user, Deal $deal): bool
    {
        return $user->can('deals.view') && $user->current_account_id === $deal->account_id;
    }

    public function create(User $user): bool
    {
        return $user->can('deals.create');
    }

    public function update(User $user, Deal $deal): bool
    {
        return $user->can('deals.update') && $user->current_account_id === $deal->account_id;
    }

    public function delete(User $user, Deal $deal): bool
    {
        return $user->can('deals.delete') && $user->current_account_id === $deal->account_id;
    }

    public function move(User $user, Deal $deal): bool
    {
        return $user->can('deals.move') && $user->current_account_id === $deal->account_id;
    }
}
