<?php

namespace App\Models\CRM;

use App\Enums\DealStatus;
use App\Models\Concerns\BelongsToAccount;
use App\Models\User;
use Database\Factories\CRM\DealFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'account_id', 'pipeline_id', 'stage_id', 'company_id', 'contact_id', 'owner_id',
    'title', 'amount', 'currency', 'probability',
    'expected_close_date', 'closed_at', 'status', 'lost_reason',
])]
class Deal extends Model
{
    /** @use HasFactory<DealFactory> */
    use BelongsToAccount, HasFactory, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'probability' => 'integer',
            'expected_close_date' => 'date',
            'closed_at' => 'datetime',
            'status' => DealStatus::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('deals')
            ->logOnly(['title', 'amount', 'currency', 'stage_id', 'status', 'owner_id', 'expected_close_date', 'probability'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'stage_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
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
