<?php

namespace App\Models\CRM;

use App\Models\Concerns\BelongsToAccount;
use Database\Factories\CRM\PipelineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable(['account_id', 'name', 'slug', 'is_default', 'position'])]
class Pipeline extends Model
{
    /** @use HasFactory<PipelineFactory> */
    use BelongsToAccount, HasFactory, LogsActivity;

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('pipelines')
            ->logOnly(['name', 'slug', 'is_default', 'position'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function stages(): HasMany
    {
        return $this->hasMany(PipelineStage::class)->orderBy('position');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }
}
