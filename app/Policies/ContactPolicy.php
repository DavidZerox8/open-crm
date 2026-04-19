<?php

namespace App\Policies;

use App\Models\CRM\Contact;
use App\Models\User;

class ContactPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('contacts.view');
    }

    public function view(User $user, Contact $contact): bool
    {
        return $user->can('contacts.view') && $user->current_account_id === $contact->account_id;
    }

    public function create(User $user): bool
    {
        return $user->can('contacts.create');
    }

    public function update(User $user, Contact $contact): bool
    {
        return $user->can('contacts.update') && $user->current_account_id === $contact->account_id;
    }

    public function delete(User $user, Contact $contact): bool
    {
        return $user->can('contacts.delete') && $user->current_account_id === $contact->account_id;
    }
}
