<?php

namespace App\Domains\TimeTracking\Actions;

use App\Domains\TimeTracking\Models\TimeEntry;

class UpdateTimeEntryAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(TimeEntry $entry, array $attributes): TimeEntry
    {
        $entry->fill($attributes);
        $entry->save();

        return $entry->refresh();
    }
}
