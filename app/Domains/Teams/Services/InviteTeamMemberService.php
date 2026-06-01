<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\CheckUserMembershipForEmailAction;
use App\Domains\Teams\Actions\CreateInvitationAction;
use App\Domains\Teams\Actions\FindActiveInvitationForTeamEmailAction;
use App\Domains\Teams\DTOs\CreateInvitationData;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Mail\TeamInvitationMail;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Models\TeamInvitation;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

final class InviteTeamMemberService
{
    public function __construct(
        private CheckUserMembershipForEmailAction $checkMembership,
        private FindActiveInvitationForTeamEmailAction $findActiveInvitation,
        private CreateInvitationAction $createInvitation,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public function execute(User $actor, Team $team, array $input): TeamInvitation
    {
        Gate::forUser($actor)->authorize('inviteMember', $team);

        if ($team->personal_team) {
            throw ValidationException::withMessages([
                'email' => __("You can't invite members to your personal team."),
            ]);
        }

        $validated = Validator::make($input, [
            'email' => ['required', 'email:rfc'],
            'role' => ['required', Rule::enum(TeamRole::class)],
        ])->validate();

        $email = Str::lower($validated['email']);
        $role = TeamRole::from($validated['role']);

        if ($email === Str::lower((string) $actor->email)) {
            throw ValidationException::withMessages([
                'email' => __("You can't invite yourself."),
            ]);
        }

        if ($this->checkMembership->execute($team, $email)) {
            throw ValidationException::withMessages([
                'email' => __(':email is already on this team.', ['email' => $email]),
            ]);
        }

        if ($this->findActiveInvitation->execute($team, $email) !== null) {
            throw ValidationException::withMessages([
                'email' => __("There's already a pending invitation for :email. Use Resend instead.", ['email' => $email]),
            ]);
        }

        $plainToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $plainToken);
        $now = CarbonImmutable::now();

        $invitation = DB::transaction(fn () => $this->createInvitation->execute(new CreateInvitationData(
            teamId: $team->id,
            email: $email,
            role: $role,
            tokenHash: $tokenHash,
            expiresAt: $now->addDays(7),
            invitedByUserId: $actor->id,
            lastSentAt: $now,
        )));

        try {
            Mail::to($email)->send(new TeamInvitationMail($invitation, $plainToken));
        } catch (Throwable $e) {
            Log::warning('Team invitation mail failed to send', [
                'invitation_id' => $invitation->id,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            throw ValidationException::withMessages([
                'email' => __('Invitation saved, but the email could not be sent right now. You can Resend it from the pending list.'),
            ]);
        }

        return $invitation;
    }
}
