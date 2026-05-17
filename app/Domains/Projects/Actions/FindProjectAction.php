<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\Project;

class FindProjectAction
{
    public function execute(int $id): Project
    {
        return Project::query()->findOrFail($id);
    }
}
