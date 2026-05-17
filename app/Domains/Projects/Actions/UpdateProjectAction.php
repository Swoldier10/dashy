<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\Project;

class UpdateProjectAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Project $project, array $attributes): Project
    {
        $project->forceFill($attributes);
        $project->save();

        return $project->refresh();
    }
}
