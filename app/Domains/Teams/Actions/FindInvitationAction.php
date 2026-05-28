<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\TeamInvitation;

class FindInvitationAction
{
    public function execute(int $invitationId): ?TeamInvitation
    {
        return TeamInvitation::query()
            ->with(['team', 'invitedBy'])
            ->find($invitationId);
    }
}
