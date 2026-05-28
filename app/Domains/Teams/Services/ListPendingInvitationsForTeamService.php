<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\ListPendingInvitationsForTeamAction;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

final class ListPendingInvitationsForTeamService
{
    public function __construct(
        private ListPendingInvitationsForTeamAction $listAction,
    ) {}

    public function execute(User $actor, Team $team): Collection
    {
        Gate::forUser($actor)->authorize('manageInvitations', $team);

        return $this->listAction->execute($team);
    }
}
