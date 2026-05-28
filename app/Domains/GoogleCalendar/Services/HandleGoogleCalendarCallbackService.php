<?php

namespace App\Domains\GoogleCalendar\Services;

use App\Domains\GoogleCalendar\Actions\CreateGoogleCalendarConnectionAction;
use App\Domains\GoogleCalendar\Actions\FindGoogleCalendarConnectionForUserAction;
use App\Domains\GoogleCalendar\Actions\UpdateGoogleCalendarConnectionAction;
use App\Domains\GoogleCalendar\Exceptions\GoogleCalendarSyncException;
use App\Domains\GoogleCalendar\Jobs\SyncGoogleCalendarJob;
use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

/**
 * Exchanges the OAuth code for tokens, persists the connection, and kicks off
 * the bootstrap sync. Verifies the user actually granted the calendar.events
 * scope — Google can return success while granting less than we asked for.
 */
final class HandleGoogleCalendarCallbackService
{
    public function __construct(
        private FindGoogleCalendarConnectionForUserAction $find,
        private CreateGoogleCalendarConnectionAction $create,
        private UpdateGoogleCalendarConnectionAction $update,
    ) {}

    public function execute(User $user): GoogleCalendarConnection
    {
        try {
            /** @var Provider $driver */
            $driver = Socialite::driver('google');
            $socialiteUser = $driver->redirectUrl(route('google-calendar.callback'))->user();
        } catch (Throwable $e) {
            report($e);
            throw new GoogleCalendarSyncException('Google authentication failed.', 0, $e);
        }

        $this->assertCalendarScopeGranted($socialiteUser);

        $attributes = $this->attributesFromSocialite($user, $socialiteUser);

        $connection = DB::transaction(function () use ($user, $attributes) {
            $existing = $this->find->execute($user);
            if ($existing !== null) {
                return $this->update->execute($existing, $attributes);
            }

            return $this->create->execute(['user_id' => $user->id] + $attributes);
        });

        SyncGoogleCalendarJob::dispatch($connection->user_id);

        return $connection;
    }

    private function assertCalendarScopeGranted(SocialiteUser $socialiteUser): void
    {
        $scopes = property_exists($socialiteUser, 'approvedScopes')
            ? (array) ($socialiteUser->approvedScopes ?? [])
            : [];

        if (! in_array(ConnectGoogleCalendarService::SCOPE_CALENDAR_EVENTS, $scopes, true)) {
            throw new GoogleCalendarSyncException(
                'Calendar access was not granted. Please re-authorize and approve the calendar permission.'
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function attributesFromSocialite(User $user, SocialiteUser $socialiteUser): array
    {
        $expiresIn = property_exists($socialiteUser, 'expiresIn') ? $socialiteUser->expiresIn : null;
        $expiresAt = is_numeric($expiresIn) ? Carbon::now()->addSeconds((int) $expiresIn) : null;

        $refreshToken = property_exists($socialiteUser, 'refreshToken') ? $socialiteUser->refreshToken : null;
        $approvedScopes = property_exists($socialiteUser, 'approvedScopes')
            ? (array) ($socialiteUser->approvedScopes ?? [])
            : [];

        return [
            'access_token' => $socialiteUser->token,
            // Google only returns refresh_token on first grant (or with prompt=consent).
            // Preserve the existing one when not returned to avoid wiping a valid token.
            'refresh_token' => $refreshToken ?? $this->find->execute($user)?->refresh_token,
            'expires_at' => $expiresAt,
            'scope' => implode(' ', $approvedScopes) ?: null,
            'account_email' => $socialiteUser->getEmail(),
            'calendar_id' => 'primary',
            'last_sync_error' => null,
            'last_sync_error_at' => null,
        ];
    }
}
