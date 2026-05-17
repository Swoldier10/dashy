<?php

namespace App\Domains\TimeTracking\Services;

use App\Domains\TimeTracking\Actions\UpdateTimeEntryAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Domains\TimeTracking\Support\DurationParser;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class UpdateTimeEntryService
{
    public function __construct(
        private UpdateTimeEntryAction $update,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public function execute(User $actor, TimeEntry $entry, array $input): TimeEntry
    {
        Gate::forUser($actor)->authorize('update', $entry);

        if ($entry->isRunning()) {
            throw ValidationException::withMessages([
                'timer' => __('Stop the timer before editing this entry.'),
            ]);
        }

        $validated = Validator::make($input, [
            'duration' => ['nullable', 'string', 'max:32'],
            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ])->validate();

        $startedAt = isset($validated['started_at']) ? Carbon::parse($validated['started_at']) : $entry->started_at;
        $endedAt = isset($validated['ended_at']) ? Carbon::parse($validated['ended_at']) : $entry->ended_at;
        $duration = $entry->duration_seconds;

        if (array_key_exists('duration', $validated) && $validated['duration'] !== null && $validated['duration'] !== '') {
            $duration = DurationParser::parse($validated['duration']);
            if (! isset($validated['ended_at']) && ! isset($validated['started_at'])) {
                $endedAt = $startedAt->copy()->addSeconds($duration);
            }
        }

        if (isset($validated['started_at']) || isset($validated['ended_at'])) {
            if ($endedAt === null || ! $endedAt->greaterThan($startedAt)) {
                throw ValidationException::withMessages([
                    'ended_at' => __('End time must be after start time.'),
                ]);
            }
            $duration = (int) $endedAt->diffInSeconds($startedAt, true);
        }

        return DB::transaction(fn () => $this->update->execute($entry, [
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'duration_seconds' => $duration,
            'notes' => array_key_exists('notes', $validated) ? $validated['notes'] : $entry->notes,
        ]));
    }
}
