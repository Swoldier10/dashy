<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\ProjectStatus;

class CreateProjectStatusAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): ProjectStatus
    {
        $status = new ProjectStatus;
        $status->forceFill([
            'project_id' => $attributes['project_id'],
            'category' => $attributes['category'],
            'name' => $attributes['name'],
            'position' => $attributes['position'],
        ]);
        $status->save();

        return $status->refresh();
    }
}
