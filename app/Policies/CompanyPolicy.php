<?php

namespace App\Policies;

use App\Models\CRM\Company;
use App\Models\User;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('companies.view');
    }

    public function view(User $user, Company $company): bool
    {
        return $user->can('companies.view') && $user->current_account_id === $company->account_id;
    }

    public function create(User $user): bool
    {
        return $user->can('companies.create');
    }

    public function update(User $user, Company $company): bool
    {
        return $user->can('companies.update') && $user->current_account_id === $company->account_id;
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->can('companies.delete') && $user->current_account_id === $company->account_id;
    }
}
