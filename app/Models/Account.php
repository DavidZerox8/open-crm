<?php

namespace App\Models;

use App\Models\CRM\Activity;
use App\Models\CRM\Company;
use App\Models\CRM\Contact;
use App\Models\CRM\Deal;
use App\Models\CRM\Lead;
use App\Models\CRM\Pipeline;
use App\Models\CRM\Task;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable(['name', 'slug'])]
class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use HasFactory, LogsActivity;

    /**
     * Configure activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('accounts')
            ->logOnly(['name', 'slug'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /**
     * Get the users that belong to this account.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('is_owner')
            ->withTimestamps();
    }

    /**
     * Get the roles that belong to this account.
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function pipelines(): HasMany
    {
        return $this->hasMany(Pipeline::class);
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
