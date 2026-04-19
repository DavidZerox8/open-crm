<?php

namespace App\Models\CRM;

use App\Enums\ActivityType;
use App\Models\Concerns\BelongsToAccount;
use App\Models\User;
use Database\Factories\CRM\ActivityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'account_id', 'user_id', 'subject_type', 'subject_id',
    'type', 'title', 'body', 'occurred_at',
])]
class Activity extends Model
{
    /** @use HasFactory<ActivityFactory> */
    use BelongsToAccount, HasFactory;

    protected $table = 'activities';

    protected function casts(): array
    {
        return [
            'type' => ActivityType::class,
            'occurred_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
