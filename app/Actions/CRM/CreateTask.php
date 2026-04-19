<?php

namespace App\Actions\CRM;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\CRM\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CreateTask
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(User $user, array $attributes, ?Model $subject = null): Task
    {
        return Task::create([
            'account_id' => $user->current_account_id,
            'assigned_to' => $attributes['assigned_to'] ?? $user->id,
            'created_by' => $user->id,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'title' => $attributes['title'],
            'description' => $attributes['description'] ?? null,
            'due_at' => $attributes['due_at'] ?? null,
            'priority' => $attributes['priority'] ?? TaskPriority::Medium,
            'status' => TaskStatus::Pending,
        ]);
    }
}
