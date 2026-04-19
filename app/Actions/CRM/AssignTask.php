<?php

namespace App\Actions\CRM;

use App\Models\CRM\Task;
use App\Models\User;

class AssignTask
{
    public function execute(Task $task, User $assignee): Task
    {
        $task->assigned_to = $assignee->id;
        $task->save();

        return $task;
    }
}
