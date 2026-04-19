<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Account;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

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

            $role = Role::create([
                'account_id' => $account->id,
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Full access within this account.',
                'is_system' => true,
            ]);

            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'current_account_id' => $account->id,
            ]);

            $user->accounts()->attach($account->id, ['is_owner' => true]);
            $user->roles()->attach($role->id, ['account_id' => $account->id]);

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
