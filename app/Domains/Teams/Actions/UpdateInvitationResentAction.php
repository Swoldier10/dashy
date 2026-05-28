<?php

namespace App\Domains\Teams\Actions;

use App\Domains\Teams\Models\TeamInvitation;

class UpdateInvitationResentAction
{
    public function execute(TeamInvitation $invitation, string $newTokenHash): TeamInvitation
    {
        $now = now();

        $invitation->forceFill([
            'token_hash' => $newTokenHash,
            'last_sent_at' => $now,
            'expires_at' => $now->copy()->addDays(7),
        ])->save();

        return $invitation->refresh();
    }
}
