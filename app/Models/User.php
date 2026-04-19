<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'current_account_id'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'current_account_id' => 'integer',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the account currently selected by the user.
     */
    public function currentAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'current_account_id');
    }

    /**
     * Get all accounts that the user belongs to.
     */
    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class)
            ->withPivot('is_owner')
            ->withTimestamps();
    }

    /**
     * Get the roles assigned to this user across accounts.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'account_user_role')
            ->withPivot('account_id')
            ->withTimestamps();
    }

    /**
     * Determine whether the user has a role in the given account.
     */
    public function hasRole(string $roleSlug, ?Account $account = null): bool
    {
        $account ??= $this->currentAccount;

        if ($account === null) {
            return false;
        }

        return $this->roles()
            ->where('roles.slug', $roleSlug)
            ->wherePivot('account_id', $account->id)
            ->exists();
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
