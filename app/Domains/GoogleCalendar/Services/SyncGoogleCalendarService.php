<?php

namespace App\Domains\GoogleCalendar\Services;

use App\Domains\GoogleCalendar\Actions\FindGoogleCalendarConnectionForUserAction;
use App\Domains\GoogleCalendar\Actions\UpdateGoogleCalendarConnectionAction;
use App\Domains\GoogleCalendar\DTOs\SyncOutcome;
use App\Domains\GoogleCalendar\Exceptions\GoogleCalendarConnectionRevokedException;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Sole entry point for a full two-way sync run for one user. Sequence:
 *   1. Refresh the access token if expired.
 *   2. Pull deltas from Google (incremental via sync_token).
 *   3. Push dirty local events/tasks + remote-delete orphans.
 *   4. Record last_synced_at and clear any prior error state.
 *
 * Each sync item is wrapped in its own transaction inside the pull/push
 * services so partial Google failures don't roll back work that already
 * succeeded.
 */
final class SyncGoogleCalendarService
{
    public function __construct(
        private FindGoogleCalendarConnectionForUserAction $findConnection,
        private EnsureFreshTokenService $ensureFreshToken,
        private PullEventsFromGoogleService $pull,
        private PushDirtyToGoogleService $push,
        private UpdateGoogleCalendarConnectionAction $updateConnection,
    ) {}

    public function execute(User $user, bool $manual = false): SyncOutcome
    {
        $connection = $this->findConnection->execute($user);
        if ($connection === null) {
            return new SyncOutcome;
        }

        $outcome = new SyncOutcome;

        try {
            $connection = $this->ensureFreshToken->execute($connection);
        } catch (GoogleCalendarConnectionRevokedException $e) {
            Log::error('Google Calendar sync aborted: connection revoked.', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);
            $outcome->errors++;

            return $outcome;
        }

        $this->pull->execute($connection->fresh(), $outcome);
        $this->push->execute($connection->fresh(), $outcome);

        DB::transaction(fn () => $this->updateConnection->execute($connection->fresh(), [
            'last_synced_at' => Carbon::now(),
            'last_sync_error' => null,
            'last_sync_error_at' => null,
        ]));

        Log::info('Google Calendar sync completed.', [
            'user_id' => $user->id,
            'manual' => $manual,
            'outcome' => $outcome->toArray(),
        ]);

        return $outcome;
    }
}
