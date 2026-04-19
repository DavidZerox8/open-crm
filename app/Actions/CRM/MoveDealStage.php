<?php

namespace App\Actions\CRM;

use App\Enums\DealStatus;
use App\Models\CRM\Deal;
use App\Models\CRM\PipelineStage;
use InvalidArgumentException;

class MoveDealStage
{
    public function execute(Deal $deal, PipelineStage $stage): Deal
    {
        if ($stage->pipeline_id !== $deal->pipeline_id) {
            throw new InvalidArgumentException('Stage does not belong to deal pipeline.');
        }

        $deal->stage_id = $stage->id;
        $deal->probability = $stage->probability;

        if ($stage->is_won) {
            $deal->status = DealStatus::Won;
            $deal->closed_at = now();
        } elseif ($stage->is_lost) {
            $deal->status = DealStatus::Lost;
            $deal->closed_at = now();
        } else {
            $deal->status = DealStatus::Open;
            $deal->closed_at = null;
        }

        $deal->save();

        return $deal;
    }
}
