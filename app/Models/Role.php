<?php

namespace App\Models;

use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory, LogsActivity;

    /**
     * Configure activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        $teamForeignKey = (string) config('permission.column_names.team_foreign_key', 'account_id');

        return LogOptions::defaults()
            ->useLogName('authorization')
            ->logOnly(['name', 'guard_name', $teamForeignKey])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /**
     * Get the account that owns this role.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(
            Account::class,
            (string) config('permission.column_names.team_foreign_key', 'account_id'),
        );
    }
}
