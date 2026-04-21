<?php

namespace App\Actions\CRM;

use App\Models\CRM\Company;

class DeleteCompany
{
    public function execute(Company $company): bool
    {
        return $company->delete();
    }
}
