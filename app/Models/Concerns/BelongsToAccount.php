<?php

namespace App\Models\Concerns;

use App\Models\Account;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

trait BelongsToAccount
{
    /**
     * Boot the trait.
     */
    public static function bootBelongsToAccount(): void
    {
        static::addGlobalScope(new class implements Scope
        {
            public function apply(Builder $builder, $model): void
            {
                $user = Auth::user();

                if ($user === null || $user->current_account_id === null) {
                    return;
                }

                $builder->where($model->qualifyColumn('account_id'), $user->current_account_id);
            }
        });

        static::creating(function ($model): void {
            if (! empty($model->account_id)) {
                return;
            }

            $user = Auth::user();

            if ($user !== null && $user->current_account_id !== null) {
                $model->account_id = $user->current_account_id;
            }
        });
    }

    /**
     * Relationship to the owning account.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
