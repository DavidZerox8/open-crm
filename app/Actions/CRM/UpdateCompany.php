<?php

namespace App\Actions\CRM;

use App\Models\CRM\Company;

class UpdateCompany
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Company $company, array $attributes): Company
    {
        $company->fill(array_filter($attributes, fn ($value) => $value !== null));
        $company->save();

        return $company;
    }
}
