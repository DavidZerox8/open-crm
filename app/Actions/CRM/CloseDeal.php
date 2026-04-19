<?php

namespace App\Actions\CRM;

use App\Enums\DealStatus;
use App\Models\CRM\Deal;

class CloseDeal
{
    public function execute(Deal $deal, DealStatus $outcome, ?string $lostReason = null): Deal
    {
        if (! in_array($outcome, [DealStatus::Won, DealStatus::Lost], true)) {
            throw new \InvalidArgumentException('Outcome must be Won or Lost.');
        }

        $deal->status = $outcome;
        $deal->closed_at = now();
        $deal->probability = $outcome === DealStatus::Won ? 100 : 0;
        $deal->lost_reason = $outcome === DealStatus::Lost ? $lostReason : null;

        $deal->save();

        return $deal;
    }
}
