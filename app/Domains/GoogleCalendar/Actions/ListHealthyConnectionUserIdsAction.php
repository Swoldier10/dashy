<?php

namespace App\Domains\GoogleCalendar\Actions;

use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use Generator;

class ListHealthyConnectionUserIdsAction
{
    /**
     * Stream the user IDs for every Google Calendar connection that is not
     * currently in an error state. Used by the scheduled sync command.
     *
     * @return Generator<int, int>
     */
    public function execute(): Generator
    {
        foreach (GoogleCalendarConnection::query()
            ->whereNull('last_sync_error_at')
            ->cursor() as $connection) {
            yield (int) $connection->user_id;
        }
    }
}
