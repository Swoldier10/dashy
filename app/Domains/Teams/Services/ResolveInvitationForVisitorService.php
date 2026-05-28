<?php

namespace App\Domains\Teams\Services;

use App\Domains\Auth\Services\UserExistsByEmailService;
use App\Domains\Teams\Actions\CheckUserMembershipForEmailAction;
use App\Domains\Teams\Actions\FindInvitationByTokenHashAction;
use App\Domains\Teams\DTOs\VisitorInvitationView;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

final class ResolveInvitationForVisitorService
{
    public function __construct(
        private FindInvitationByTokenHashAction $findInvitation,
        private CheckUserMembershipForEmailAction $checkMembership,
        private UserExistsByEmailService $userExists,
    ) {}

    public function execute(string $plainToken, ?User $authUser): VisitorInvitationView
    {
        $tokenHash = hash('sha256', $plainToken);
        $invitation = $this->findInvitation->execute($tokenHash);

        if ($invitation === null) {
            return VisitorInvitationView::invalid();
        }

        $team = $invitation->team;
        $inviter = $invitation->invitedBy;
        $boundEmail = $invitation->email;
        $expiresAt = $invitation->expires_at !== null
            ? CarbonImmutable::instance($invitation->expires_at)
            : null;

        $base = [
            'team' => $team,
            'inviter' => $inviter,
            'role' => $invitation->role,
            'boundEmail' => $boundEmail,
            'expiresAt' => $expiresAt,
            'invitation' => $invitation,
        ];

        if ($invitation->isRevoked()) {
            return new VisitorInvitationView(...$base, status: VisitorInvitationView::STATUS_REVOKED);
        }

        if ($invitation->isExpired()) {
            return new VisitorInvitationView(...$base, status: VisitorInvitationView::STATUS_EXPIRED);
        }

        if ($invitation->isAccepted()) {
            if ($authUser !== null && $invitation->accepted_by_user_id === $authUser->id) {
                return new VisitorInvitationView(...$base, status: VisitorInvitationView::STATUS_ALREADY_MEMBER);
            }

            return new VisitorInvitationView(...$base, status: VisitorInvitationView::STATUS_ACCEPTED_BY_OTHER);
        }

        // Pending below.
        if ($authUser !== null) {
            if (Str::lower((string) $boundEmail) !== Str::lower((string) $authUser->email)) {
                return new VisitorInvitationView(...$base, status: VisitorInvitationView::STATUS_EMAIL_MISMATCH);
            }

            if ($this->checkMembership->execute($team, (string) $authUser->email)) {
                return new VisitorInvitationView(...$base, status: VisitorInvitationView::STATUS_ALREADY_MEMBER);
            }

            return new VisitorInvitationView(...$base, status: VisitorInvitationView::STATUS_READY_TO_ACCEPT);
        }

        // Guest.
        if ($this->userExists->execute((string) $boundEmail)) {
            return new VisitorInvitationView(...$base, status: VisitorInvitationView::STATUS_NEEDS_LOGIN);
        }

        return new VisitorInvitationView(...$base, status: VisitorInvitationView::STATUS_NEEDS_REGISTER);
    }
}
