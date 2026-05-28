<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\FindInvitationAction;
use App\Domains\Teams\Actions\UpdateInvitationRevokedAction;
use App\Domains\Teams\Exceptions\InvalidInvitationException;
use App\Domains\Teams\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class RevokeTeamInvitationService
{
    public function __construct(
        private FindInvitationAction $findInvitation,
        private UpdateInvitationRevokedAction $updateRevoked,
    ) {}

    /**
     * @throws InvalidInvitationException
     */
    public function execute(User $actor, int $invitationId): TeamInvitation
    {
        $invitation = $this->findInvitation->execute($invitationId);
        if ($invitation === null) {
            throw new InvalidInvitationException;
        }

        Gate::forUser($actor)->authorize('manageInvitations', $invitation->team);

        if (! $invitation->isPending()) {
            throw new InvalidInvitationException;
        }

        return DB::transaction(fn () => $this->updateRevoked->execute($invitation));
    }
}
