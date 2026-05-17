<?php

namespace App\Domains\TimeTracking\Actions;

use App\Domains\TimeTracking\Models\TimeEntry;

class DeleteTimeEntryAction
{
    public function execute(TimeEntry $entry): void
    {
        $entry->delete();
    }
}
