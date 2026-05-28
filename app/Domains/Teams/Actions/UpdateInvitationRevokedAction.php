<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\TeamInvitation;

class UpdateInvitationRevokedAction
{
    public function execute(TeamInvitation $invitation): TeamInvitation
    {
        $invitation->forceFill([
            'revoked_at' => now(),
        ])->save();

        return $invitation->refresh();
    }
}
