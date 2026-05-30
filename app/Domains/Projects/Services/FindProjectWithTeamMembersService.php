<?php

namespace App\Domains\Projects\Services;

use App\Domains\Projects\Actions\FindProjectWithTeamMembersAction;
use App\Domains\Projects\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class FindProjectWithTeamMembersService
{
    public function __construct(
        private FindProjectWithTeamMembersAction $find,
    ) {}

    public function execute(User $actor, int $projectId): Project
    {
        $project = $this->find->execute($projectId);

        Gate::forUser($actor)->authorize('view', $project);

        return $project;
    }
}
