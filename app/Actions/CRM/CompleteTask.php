<?php

namespace App\Actions\CRM;

use App\Enums\TaskStatus;
use App\Models\CRM\Task;

class CompleteTask
{
    public function execute(Task $task): Task
    {
        $task->status = TaskStatus::Completed;
        $task->completed_at = now();
        $task->save();

        return $task;
    }
}
