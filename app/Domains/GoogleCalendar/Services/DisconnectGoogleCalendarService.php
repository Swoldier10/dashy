<?php

namespace App\Domains\GoogleCalendar\Services;

use App\Domains\GoogleCalendar\Actions\DeleteGoogleCalendarConnectionAction;
use App\Domains\GoogleCalendar\Actions\FindGoogleCalendarConnectionForUserAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class DisconnectGoogleCalendarService
{
    private const REVOKE_URL = 'https://oauth2.googleapis.com/revoke';

    public function __construct(
        private FindGoogleCalendarConnectionForUserAction $find,
        private DeleteGoogleCalendarConnectionAction $delete,
    ) {}

    public function execute(User $user): void
    {
        $connection = $this->find->execute($user);
        if ($connection === null) {
            return;
        }

        $token = $connection->refresh_token ?? $connection->access_token;
        if ($token !== null) {
            $this->revokeRemote($token);
        }

        DB::transaction(fn () => $this->delete->execute($connection));
    }

    private function revokeRemote(string $token): void
    {
        try {
            Http::asForm()->post(self::REVOKE_URL, ['token' => $token]);
        } catch (Throwable $e) {
            // Revocation is best-effort; the local connection is removed regardless.
            Log::info('Google Calendar token revocation failed; deleting local connection anyway.', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
