<?php

namespace App\Domains\TimeTracking\Actions;

use App\Domains\TimeTracking\Models\TimeEntry;

class CreateTimeEntryAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): TimeEntry
    {
        $entry = new TimeEntry;
        $entry->forceFill([
            'task_id' => $attributes['task_id'],
            'user_id' => $attributes['user_id'],
            'started_at' => $attributes['started_at'],
            'ended_at' => $attributes['ended_at'] ?? null,
            'duration_seconds' => $attributes['duration_seconds'] ?? null,
            'notes' => $attributes['notes'] ?? null,
        ]);
        $entry->save();

        return $entry->refresh();
    }
}
