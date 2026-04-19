<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Account;
use App\Models\User;
use App\Support\Authorization\RolePermissionMatrix;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Spatie\Permission\PermissionRegistrar;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(private readonly RolePermissionMatrix $rolePermissionMatrix) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input): User {
            $account = Account::create([
                'name' => $input['name']."'s Workspace",
                'slug' => $this->generateUniqueAccountSlug($input['name']),
            ]);

            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'current_account_id' => $account->id,
            ]);

            $user->accounts()->attach($account->id, ['is_owner' => true]);

            $permissionRegistrar = app(PermissionRegistrar::class);
            $previousTeamId = $permissionRegistrar->getPermissionsTeamId();
            $permissionRegistrar->setPermissionsTeamId($account->id);

            try {
                $this->rolePermissionMatrix->ensureRolesForAccount($account);
                $user->assignRole($this->rolePermissionMatrix->ownerRoleName());
            } finally {
                $permissionRegistrar->setPermissionsTeamId($previousTeamId);
            }

            activity('auth')
                ->causedBy($user)
                ->performedOn($user)
                ->event('registered')
                ->withProperties([
                    'account_id' => $account->id,
                    'role' => $this->rolePermissionMatrix->ownerRoleName(),
                ])
                ->log('User registered and workspace provisioned');

            return $user;
        });
    }

    /**
     * Generate a unique slug for a new account.
     */
    private function generateUniqueAccountSlug(string $name): string
    {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'workspace';
        }

        $slug = $baseSlug;
        $suffix = 2;

        while (Account::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
