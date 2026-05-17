<?php

namespace App\Domains\TimeTracking\Services;

use App\Domains\TimeTracking\Actions\FindActiveTimerForUserAction;
use App\Domains\TimeTracking\Actions\UpdateTimeEntryAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class StopTimerService
{
    public function __construct(
        private FindActiveTimerForUserAction $findActive,
        private UpdateTimeEntryAction $update,
    ) {}

    public function execute(User $actor): TimeEntry
    {
        return DB::transaction(function () use ($actor) {
            $running = $this->findActive->execute($actor);
            if ($running === null) {
                throw ValidationException::withMessages([
                    'timer' => __('No timer is currently running.'),
                ]);
            }

            $now = Carbon::now();
            $duration = max(1, (int) $now->diffInSeconds($running->started_at, true));

            return $this->update->execute($running, [
                'ended_at' => $now,
                'duration_seconds' => $duration,
            ]);
        });
    }
}
