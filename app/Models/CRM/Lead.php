<?php

namespace App\Models\CRM;

use App\Enums\LeadStatus;
use App\Models\Concerns\BelongsToAccount;
use App\Models\User;
use Database\Factories\CRM\LeadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'account_id', 'owner_id', 'company_name', 'contact_name', 'email', 'phone',
    'source', 'status', 'score', 'notes',
    'converted_at', 'converted_company_id', 'converted_contact_id', 'converted_deal_id',
])]
class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use BelongsToAccount, HasFactory, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
            'score' => 'integer',
            'converted_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('leads')
            ->logOnly(['contact_name', 'company_name', 'email', 'phone', 'status', 'owner_id', 'source', 'score'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function convertedCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'converted_company_id');
    }

    public function convertedContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'converted_contact_id');
    }

    public function convertedDeal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'converted_deal_id');
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
