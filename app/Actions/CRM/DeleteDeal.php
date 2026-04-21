<?php

namespace App\Actions\CRM;

use App\Models\CRM\Deal;

class DeleteDeal
{
    public function execute(Deal $deal): bool
    {
        return $deal->delete();
    }
}
