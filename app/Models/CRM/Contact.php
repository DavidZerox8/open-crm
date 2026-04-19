<?php

namespace App\Models\CRM;

use App\Models\Concerns\BelongsToAccount;
use App\Models\User;
use Database\Factories\CRM\ContactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'account_id', 'company_id', 'owner_id', 'first_name', 'last_name',
    'job_title', 'email', 'phone', 'mobile', 'notes',
])]
class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use BelongsToAccount, HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('contacts')
            ->logOnly(['first_name', 'last_name', 'email', 'phone', 'job_title', 'company_id', 'owner_id'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function fullName(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
    }

    public function initials(): string
    {
        return Str::of($this->fullName())
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'subject');
    }
}
