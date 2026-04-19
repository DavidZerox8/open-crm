<?php

namespace App\Actions\CRM;

use App\Models\CRM\Contact;

class UpdateContact
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Contact $contact, array $attributes): Contact
    {
        $contact->fill(array_filter($attributes, fn ($value) => $value !== null));
        $contact->save();

        return $contact;
    }
}
