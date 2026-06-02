<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\AttachTeamMemberAction;
use App\Domains\Teams\Actions\FindAndLockInvitationByTokenHashAction;
use App\Domains\Teams\Actions\UpdateInvitationAcceptedAction;
use App\Domains\Teams\Events\TeamInvitationAccepted;
use App\Domains\Teams\Events\TeamMemberJoined;
use App\Domains\Teams\Exceptions\InvalidInvitationException;
use App\Domains\Teams\Exceptions\InvitationAlreadyAcceptedException;
use App\Domains\Teams\Exceptions\InvitationEmailMismatchException;
use App\Domains\Teams\Exceptions\InvitationExpiredException;
use App\Domains\Teams\Exceptions\InvitationRevokedException;
use App\Domains\Teams\Models\TeamInvitation;
use App\Models\User;
use App\Support\Concerns\DetectsUniqueConstraintViolations;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class AcceptTeamInvitationService
{
    use DetectsUniqueConstraintViolations;

    public function __construct(
        private FindAndLockInvitationByTokenHashAction $findAndLock,
        private AttachTeamMemberAction $attachMember,
        private UpdateInvitationAcceptedAction $updateAccepted,
        private ListTeamMemberIdsService $listTeamMemberIds,
    ) {}

    /**
     * @throws InvalidInvitationException
     * @throws InvitationRevokedException
     * @throws InvitationExpiredException
     * @throws InvitationEmailMismatchException
     * @throws InvitationAlreadyAcceptedException
     */
    public function execute(User $user, string $plainToken): TeamInvitation
    {
        $tokenHash = hash('sha256', $plainToken);

        return DB::transaction(function () use ($user, $tokenHash) {
            $invitation = $this->findAndLock->execute($tokenHash);
            if ($invitation === null) {
                throw new InvalidInvitationException;
            }

            if ($invitation->isRevoked()) {
                throw new InvitationRevokedException;
            }

            if ($invitation->isExpired()) {
                throw new InvitationExpiredException;
            }

            if (Str::lower((string) $invitation->email) !== Str::lower((string) $user->email)) {
                throw new InvitationEmailMismatchException(boundEmail: $invitation->email);
            }

            if ($invitation->isAccepted()) {
                if ($invitation->accepted_by_user_id === $user->id) {
                    return $invitation;
                }
                throw new InvitationAlreadyAcceptedException;
            }

            $becameMember = true;

            try {
                $this->attachMember->execute($invitation->team, $user, $invitation->role);
            } catch (QueryException $e) {
                if (! $this->isUniqueViolation($e)) {
                    throw $e;
                }
                // User is already on the team via a different path. Treat as
                // success: still mark the invitation consumed below.
                $becameMember = false;
            }

            $accepted = $this->updateAccepted->execute($invitation, $user);

            $team = $invitation->team;
            $invitedByUserId = $invitation->invited_by_user_id !== null ? (int) $invitation->invited_by_user_id : null;
            $otherMemberIds = array_values(array_diff($this->listTeamMemberIds->execute($team), [$user->id]));

            DB::afterCommit(function () use ($team, $user, $invitedByUserId, $becameMember, $otherMemberIds): void {
                event(TeamInvitationAccepted::fromTeam($team, $invitedByUserId, $user));

                if ($becameMember) {
                    event(TeamMemberJoined::fromTeam($team, $user, $otherMemberIds, $invitedByUserId));
                }
            });

            return $accepted;
        });
    }
}
