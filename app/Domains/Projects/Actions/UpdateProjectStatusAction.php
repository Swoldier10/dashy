<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\ProjectStatus;

class UpdateProjectStatusAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(ProjectStatus $status, array $attributes): ProjectStatus
    {
        $status->forceFill($attributes);
        $status->save();

        return $status->refresh();
    }
}
