<?php

namespace App\Domains\GoogleCalendar\Services;

use App\Domains\GoogleCalendar\Actions\UpdateGoogleCalendarConnectionAction;
use App\Domains\GoogleCalendar\Exceptions\GoogleCalendarConnectionRevokedException;
use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Refreshes the Google OAuth access token when it's expired or about to. On
 * an unrecoverable refresh failure (invalid_grant — the user revoked Dashy's
 * grant), marks the connection so the scheduled command stops dispatching
 * sync jobs and the UI shows a "Reconnect" prompt.
 */
final class EnsureFreshTokenService
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const REFRESH_LEEWAY_SECONDS = 60;

    public function __construct(
        private UpdateGoogleCalendarConnectionAction $update,
    ) {}

    public function execute(GoogleCalendarConnection $connection): GoogleCalendarConnection
    {
        if (! $this->needsRefresh($connection)) {
            return $connection;
        }

        if ($connection->refresh_token === null) {
            $this->markRevoked($connection, 'Refresh token missing. Please reconnect.');
            throw new GoogleCalendarConnectionRevokedException('Google Calendar connection is missing a refresh token.');
        }

        try {
            $response = Http::asForm()
                ->post(self::TOKEN_URL, [
                    'client_id' => (string) config('services.google.client_id'),
                    'client_secret' => (string) config('services.google.client_secret'),
                    'refresh_token' => $connection->refresh_token,
                    'grant_type' => 'refresh_token',
                ]);
        } catch (Throwable $e) {
            // Network-level failure — bubble up so the queue retries the job.
            throw $e;
        }

        if ($response->status() === 400 && $this->isInvalidGrant($response->json())) {
            $this->markRevoked($connection, 'Google Calendar access was revoked. Please reconnect.');
            throw new GoogleCalendarConnectionRevokedException('Google refused the refresh token (invalid_grant).');
        }

        $response->throw();

        $body = $response->json();
        $accessToken = is_string($body['access_token'] ?? null) ? $body['access_token'] : null;
        $expiresIn = is_numeric($body['expires_in'] ?? null) ? (int) $body['expires_in'] : null;

        if ($accessToken === null) {
            throw new GoogleCalendarConnectionRevokedException('Google token refresh response was incomplete.');
        }

        return DB::transaction(fn () => $this->update->execute($connection, [
            'access_token' => $accessToken,
            'expires_at' => $expiresIn !== null ? Carbon::now()->addSeconds($expiresIn) : null,
        ]));
    }

    private function needsRefresh(GoogleCalendarConnection $connection): bool
    {
        if ($connection->expires_at === null) {
            return false;
        }

        return $connection->expires_at->lessThanOrEqualTo(
            Carbon::now()->addSeconds(self::REFRESH_LEEWAY_SECONDS)
        );
    }

    /**
     * @param  mixed  $body
     */
    private function isInvalidGrant($body): bool
    {
        return is_array($body) && ($body['error'] ?? null) === 'invalid_grant';
    }

    private function markRevoked(GoogleCalendarConnection $connection, string $message): void
    {
        DB::transaction(fn () => $this->update->execute($connection, [
            'last_sync_error' => $message,
            'last_sync_error_at' => Carbon::now(),
        ]));
    }
}
