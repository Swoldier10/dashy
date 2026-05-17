<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\ProjectStatus;

class FindProjectStatusAction
{
    public function execute(int $id): ProjectStatus
    {
        return ProjectStatus::query()->findOrFail($id);
    }
}
