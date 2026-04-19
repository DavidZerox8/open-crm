<?php

namespace App\Actions\Account;

use App\Models\Account;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Spatie\Permission\PermissionRegistrar;

class SwitchAccountAction
{
    public function execute(User $user, Account $account): void
    {
        if (! $user->accounts()->whereKey($account->id)->exists()) {
            throw new AuthorizationException('El usuario no pertenece a esta cuenta.');
        }

        $user->forceFill(['current_account_id' => $account->id])->save();

        app(PermissionRegistrar::class)->setPermissionsTeamId($account->id);
    }
}
