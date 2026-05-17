<?php

namespace App\Domains\TimeTracking\Services;

use App\Domains\Tasks\Models\Task;
use App\Domains\TimeTracking\Actions\CreateTimeEntryAction;
use App\Domains\TimeTracking\Actions\FindActiveTimerForUserAction;
use App\Domains\TimeTracking\Actions\UpdateTimeEntryAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class StartTimerService
{
    public function __construct(
        private FindActiveTimerForUserAction $findActive,
        private UpdateTimeEntryAction $update,
        private CreateTimeEntryAction $create,
    ) {}

    public function execute(User $actor, Task $task): TimeEntry
    {
        Gate::forUser($actor)->authorize('create', [TimeEntry::class, $task]);

        return DB::transaction(function () use ($actor, $task) {
            $running = $this->findActive->execute($actor);
            $now = Carbon::now();

            if ($running !== null) {
                $duration = max(1, (int) $now->diffInSeconds($running->started_at, true));
                $this->update->execute($running, [
                    'ended_at' => $now,
                    'duration_seconds' => $duration,
                ]);
            }

            return $this->create->execute([
                'task_id' => $task->id,
                'user_id' => $actor->id,
                'started_at' => $now,
            ]);
        });
    }
}
