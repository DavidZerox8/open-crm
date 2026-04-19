<?php

namespace App\Actions\CRM;

use App\Enums\DealStatus;
use App\Models\CRM\Deal;
use App\Models\CRM\Pipeline;
use App\Models\User;

class CreateDeal
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(User $user, array $attributes): Deal
    {
        $pipelineId = $attributes['pipeline_id'] ?? Pipeline::query()
            ->where('account_id', $user->current_account_id)
            ->orderByDesc('is_default')
            ->orderBy('position')
            ->value('id');

        $stageId = $attributes['stage_id'] ?? Pipeline::findOrFail($pipelineId)
            ->stages()
            ->orderBy('position')
            ->value('id');

        return Deal::create([
            'account_id' => $user->current_account_id,
            'pipeline_id' => $pipelineId,
            'stage_id' => $stageId,
            'company_id' => $attributes['company_id'] ?? null,
            'contact_id' => $attributes['contact_id'] ?? null,
            'owner_id' => $attributes['owner_id'] ?? $user->id,
            'title' => $attributes['title'],
            'amount' => $attributes['amount'] ?? 0,
            'currency' => $attributes['currency'] ?? 'EUR',
            'probability' => $attributes['probability'] ?? 0,
            'expected_close_date' => $attributes['expected_close_date'] ?? null,
            'status' => $attributes['status'] ?? DealStatus::Open,
        ]);
    }
}
