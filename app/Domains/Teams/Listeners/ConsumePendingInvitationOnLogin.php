<?php

namespace App\Domains\Teams\Listeners;

use App\Domains\Teams\Exceptions\TeamInvitationException;
use App\Domains\Teams\Services\AcceptTeamInvitationService;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ConsumePendingInvitationOnLogin
{
    public function __construct(
        private AcceptTeamInvitationService $accept,
    ) {}

    public function handle(Login $event): void
    {
        $token = session()->pull('invitation.pending_token');
        if (! is_string($token) || $token === '') {
            return;
        }

        $user = $event->user;
        if (! $user instanceof User) {
            return;
        }

        try {
            $invitation = $this->accept->execute($user, $token);
        } catch (TeamInvitationException) {
            return;
        } catch (Throwable $e) {
            Log::warning('Failed to auto-accept invitation on login', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return;
        }

        session()->put('url.intended', route('teams.show', $invitation->team));
    }
}
