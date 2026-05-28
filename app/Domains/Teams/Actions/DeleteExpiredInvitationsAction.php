<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\TeamInvitation;

class DeleteExpiredInvitationsAction
{
    public function execute(): int
    {
        return TeamInvitation::query()
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '<', now())
            ->delete();
    }
}
