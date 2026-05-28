<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\Team;
use Illuminate\Database\Eloquent\Collection;

class ListPendingInvitationsForTeamAction
{
    public function execute(Team $team): Collection
    {
        return $team->invitations()
            ->with('invitedBy')
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->orderByDesc('created_at')
            ->get();
    }
}
