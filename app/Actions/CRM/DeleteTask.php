<?php

namespace App\Actions\CRM;

use App\Models\CRM\Task;

class DeleteTask
{
    public function execute(Task $task): bool
    {
        return $task->delete();
    }
}
