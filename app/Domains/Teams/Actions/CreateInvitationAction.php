<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\DTOs\CreateInvitationData;
use App\Domains\Teams\Models\TeamInvitation;

class CreateInvitationAction
{
    public function execute(CreateInvitationData $data): TeamInvitation
    {
        $invitation = new TeamInvitation;
        $invitation->forceFill([
            'team_id' => $data->teamId,
            'email' => $data->email,
            'role' => $data->role->value,
            'token_hash' => $data->tokenHash,
            'expires_at' => $data->expiresAt,
            'invited_by_user_id' => $data->invitedByUserId,
            'last_sent_at' => $data->lastSentAt,
        ]);
        $invitation->save();

        return $invitation->refresh();
    }
}
