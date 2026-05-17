<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\Project;

class CreateProjectAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Project
    {
        $project = new Project;
        $project->forceFill([
            'team_id' => $attributes['team_id'],
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'logo' => $attributes['logo'] ?? null,
        ]);
        $project->save();

        return $project->refresh();
    }
}
