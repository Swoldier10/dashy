<?php

namespace App\Domains\TimeTracking\Actions;

use App\Domains\TimeTracking\Models\TimeEntry;

class FindTimeEntryAction
{
    public function execute(int $id): TimeEntry
    {
        return TimeEntry::query()->findOrFail($id);
    }
}
