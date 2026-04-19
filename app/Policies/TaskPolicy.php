<?php

namespace App\Policies;

use App\Models\CRM\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('tasks.view');
    }

    public function view(User $user, Task $task): bool
    {
        return $user->can('tasks.view') && $user->current_account_id === $task->account_id;
    }

    public function create(User $user): bool
    {
        return $user->can('tasks.create');
    }

    public function update(User $user, Task $task): bool
    {
        return $user->can('tasks.update') && $user->current_account_id === $task->account_id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->can('tasks.delete') && $user->current_account_id === $task->account_id;
    }

    public function assign(User $user, Task $task): bool
    {
        return $user->can('tasks.assign') && $user->current_account_id === $task->account_id;
    }

    public function complete(User $user, Task $task): bool
    {
        return $user->can('tasks.complete')
            && $user->current_account_id === $task->account_id
            && ($task->assigned_to === $user->id || $user->can('tasks.update'));
    }
}
