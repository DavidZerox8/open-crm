<?php

namespace App\Actions\CRM;

use App\Models\CRM\Deal;

class UpdateDeal
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Deal $deal, array $attributes): Deal
    {
        $deal->fill(array_filter($attributes, fn ($value) => $value !== null));
        $deal->save();

        return $deal;
    }
}
