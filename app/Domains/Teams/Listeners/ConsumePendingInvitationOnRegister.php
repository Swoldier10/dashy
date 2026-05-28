<?php

namespace App\Domains\Teams\Listeners;

use App\Domains\Teams\Exceptions\TeamInvitationException;
use App\Domains\Teams\Services\AcceptTeamInvitationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ConsumePendingInvitationOnRegister
{
    public function __construct(
        private AcceptTeamInvitationService $accept,
    ) {}

    public function handle(Registered $event): void
    {
        $token = session()->pull('invitation.pending_token');
        if (! is_string($token) || $token === '') {
            return;
        }

        $user = $event->user;
        if (! $user instanceof Authenticatable) {
            return;
        }

        try {
            $invitation = $this->accept->execute($user, $token);
        } catch (TeamInvitationException) {
            return;
        } catch (Throwable $e) {
            Log::warning('Failed to auto-accept invitation on register', [
                'user_id' => $user->getAuthIdentifier(),
                'error' => $e->getMessage(),
            ]);

            return;
        }

        session()->put('url.intended', route('teams.show', $invitation->team));
    }
}
