<?php

namespace App\Models\CRM;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Concerns\BelongsToAccount;
use App\Models\User;
use Database\Factories\CRM\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'account_id', 'assigned_to', 'created_by', 'subject_type', 'subject_id',
    'title', 'description', 'due_at', 'completed_at', 'priority', 'status',
])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use BelongsToAccount, HasFactory, LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'priority' => TaskPriority::class,
            'status' => TaskStatus::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('tasks')
            ->logOnly(['title', 'due_at', 'priority', 'status', 'assigned_to', 'completed_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function isOverdue(): bool
    {
        return $this->status === TaskStatus::Pending
            && $this->due_at !== null
            && $this->due_at->isPast();
    }
}
