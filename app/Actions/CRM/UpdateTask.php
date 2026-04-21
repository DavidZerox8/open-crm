<?php

namespace App\Actions\CRM;

use App\Models\CRM\Task;

class UpdateTask
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Task $task, array $attributes): Task
    {
        $task->fill(array_filter(
            [
                'assignee_id' => $attributes['assignee_id'] ?? null,
                'title' => $attributes['title'] ?? null,
                'description' => $attributes['description'] ?? null,
                'due_at' => $attributes['due_at'] ?? null,
                'status' => $attributes['status'] ?? null,
            ],
            fn ($value) => $value !== null,
        ));

        $task->save();

        return $task;
    }
}
