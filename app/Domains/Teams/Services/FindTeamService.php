<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\FindTeamAction;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class FindTeamService
{
    public function __construct(
        private FindTeamAction $find,
    ) {}

    public function execute(User $actor, int $teamId): Team
    {
        $team = $this->find->execute($teamId);

        Gate::forUser($actor)->authorize('view', $team);

        return $team;
    }
}
