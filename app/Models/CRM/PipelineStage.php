<?php

namespace App\Models\CRM;

use App\Models\Concerns\BelongsToAccount;
use Database\Factories\CRM\PipelineStageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable(['account_id', 'pipeline_id', 'name', 'slug', 'position', 'probability', 'color', 'is_won', 'is_lost'])]
class PipelineStage extends Model
{
    /** @use HasFactory<PipelineStageFactory> */
    use BelongsToAccount, HasFactory, LogsActivity;

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'probability' => 'integer',
            'is_won' => 'boolean',
            'is_lost' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('pipeline_stages')
            ->logOnly(['name', 'position', 'probability', 'is_won', 'is_lost'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'stage_id');
    }
}
