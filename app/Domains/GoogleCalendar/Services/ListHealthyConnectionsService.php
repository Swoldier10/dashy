<?php

namespace App\Domains\GoogleCalendar\Services;

use App\Domains\GoogleCalendar\Actions\ListHealthyConnectionUserIdsAction;
use Generator;

/**
 * Yields the user IDs of every Google Calendar connection that is not in
 * an error state. Used by the scheduled sync command to fan out one
 * SyncGoogleCalendarJob per healthy connection.
 */
final class ListHealthyConnectionsService
{
    public function __construct(
        private ListHealthyConnectionUserIdsAction $list,
    ) {}

    /**
     * @return Generator<int, int>
     */
    public function execute(): Generator
    {
        yield from $this->list->execute();
    }
}
