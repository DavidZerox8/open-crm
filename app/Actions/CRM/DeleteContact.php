<?php

namespace App\Actions\CRM;

use App\Models\CRM\Contact;

class DeleteContact
{
    public function execute(Contact $contact): bool
    {
        return $contact->delete();
    }
}
