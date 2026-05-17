<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\Project;

class DeleteProjectAction
{
    public function execute(Project $project): void
    {
        $project->delete();
    }
}
