<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\ProjectStatus;

class DeleteProjectStatusAction
{
    public function execute(ProjectStatus $status): void
    {
        $status->delete();
    }
}
