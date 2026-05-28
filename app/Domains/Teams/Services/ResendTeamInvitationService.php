<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\FindInvitationAction;
use App\Domains\Teams\Actions\UpdateInvitationResentAction;
use App\Domains\Teams\Exceptions\InvalidInvitationException;
use App\Domains\Teams\Exceptions\InvitationResendThrottledException;
use App\Domains\Teams\Mail\TeamInvitationMail;
use App\Domains\Teams\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

final class ResendTeamInvitationService
{
    private const RESEND_THROTTLE_SECONDS = 3600;

    public function __construct(
        private FindInvitationAction $findInvitation,
        private UpdateInvitationResentAction $updateResent,
    ) {}

    /**
     * @throws InvalidInvitationException
     * @throws InvitationResendThrottledException
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

        $elapsed = $invitation->last_sent_at !== null
            ? (int) $invitation->last_sent_at->diffInSeconds(now(), false)
            : self::RESEND_THROTTLE_SECONDS;

        if ($elapsed < self::RESEND_THROTTLE_SECONDS) {
            throw new InvitationResendThrottledException(
                retryAfterSeconds: self::RESEND_THROTTLE_SECONDS - $elapsed,
            );
        }

        $plainToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $plainToken);

        $invitation = DB::transaction(fn () => $this->updateResent->execute($invitation, $tokenHash));

        try {
            Mail::to($invitation->email)->send(new TeamInvitationMail($invitation, $plainToken));
        } catch (Throwable $e) {
            Log::warning('Team invitation resend mail failed', [
                'invitation_id' => $invitation->id,
                'email' => $invitation->email,
                'error' => $e->getMessage(),
            ]);
            throw ValidationException::withMessages([
                'invitation' => __('Could not resend the email right now. Please try again in a moment.'),
            ]);
        }

        return $invitation;
    }
}
