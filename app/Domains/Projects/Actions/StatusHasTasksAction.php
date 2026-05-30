<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\ProjectStatus;

class StatusHasTasksAction
{
    public function execute(ProjectStatus $status): bool
    {
        return $status->tasks()->exists();
    }
}
