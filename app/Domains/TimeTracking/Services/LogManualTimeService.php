<?php

namespace App\Domains\TimeTracking\Services;

use App\Domains\Tasks\Models\Task;
use App\Domains\TimeTracking\Actions\CreateTimeEntryAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Domains\TimeTracking\Support\DurationParser;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class LogManualTimeService
{
    public function __construct(
        private CreateTimeEntryAction $create,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public function execute(User $actor, Task $task, array $input): TimeEntry
    {
        Gate::forUser($actor)->authorize('create', [TimeEntry::class, $task]);

        $validated = Validator::make($input, [
            'duration' => ['nullable', 'string', 'max:32'],
            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ])->validate();

        $startedAt = isset($validated['started_at']) ? Carbon::parse($validated['started_at']) : null;
        $endedAt = isset($validated['ended_at']) ? Carbon::parse($validated['ended_at']) : null;
        $duration = null;

        if (! empty($validated['duration'])) {
            $duration = DurationParser::parse($validated['duration']);
        }

        if ($startedAt !== null && $endedAt !== null) {
            if (! $endedAt->greaterThan($startedAt)) {
                throw ValidationException::withMessages([
                    'ended_at' => __('End time must be after start time.'),
                ]);
            }
            $duration = (int) $endedAt->diffInSeconds($startedAt, true);
        } elseif ($duration !== null) {
            if ($endedAt === null) {
                $endedAt = Carbon::now();
            }
            if ($startedAt === null) {
                $startedAt = $endedAt->copy()->subSeconds($duration);
            } else {
                $endedAt = $startedAt->copy()->addSeconds($duration);
            }
        } else {
            throw ValidationException::withMessages([
                'duration' => __('Enter a duration like "3h 20m", or provide a start and end time.'),
            ]);
        }

        if ($duration <= 0) {
            throw ValidationException::withMessages([
                'duration' => __('Duration must be greater than zero.'),
            ]);
        }

        return DB::transaction(fn () => $this->create->execute([
            'task_id' => $task->id,
            'user_id' => $actor->id,
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'duration_seconds' => $duration,
            'notes' => $validated['notes'] ?? null,
        ]));
    }
}
