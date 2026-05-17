<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\Project;

class FindProjectWithTeamMembersAction
{
    public function execute(int $projectId): Project
    {
        return Project::query()
            ->with(['team.members' => fn ($q) => $q->orderBy('users.name')])
            ->findOrFail($projectId);
    }
}
