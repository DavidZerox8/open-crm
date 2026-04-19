<?php

namespace App\Models\CRM;

use App\Models\Concerns\BelongsToAccount;
use App\Models\User;
use Database\Factories\CRM\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'account_id', 'owner_id', 'name', 'legal_name', 'industry', 'website',
    'phone', 'email', 'address', 'city', 'country', 'notes',
])]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use BelongsToAccount, HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('companies')
            ->logOnly(['name', 'legal_name', 'industry', 'website', 'phone', 'email', 'owner_id'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
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
