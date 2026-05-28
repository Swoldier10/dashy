<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\TeamInvitation;

class FindInvitationByTokenHashAction
{
    public function execute(string $tokenHash): ?TeamInvitation
    {
        return TeamInvitation::query()
            ->with(['team', 'invitedBy'])
            ->where('token_hash', $tokenHash)
            ->first();
    }
}
