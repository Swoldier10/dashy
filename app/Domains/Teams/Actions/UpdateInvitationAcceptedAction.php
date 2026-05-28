<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\TeamInvitation;
use App\Models\User;

class UpdateInvitationAcceptedAction
{
    public function execute(TeamInvitation $invitation, User $acceptedBy): TeamInvitation
    {
        $invitation->forceFill([
            'accepted_at' => now(),
            'accepted_by_user_id' => $acceptedBy->id,
        ])->save();

        return $invitation->refresh();
    }
}
