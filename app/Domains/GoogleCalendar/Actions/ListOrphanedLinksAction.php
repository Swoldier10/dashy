<?php

namespace App\Domains\GoogleCalendar\Actions;

use App\Domains\Calendar\Models\Event;
use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Domains\GoogleCalendar\Models\GoogleCalendarLink;
use App\Domains\Tasks\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class ListOrphanedLinksAction
{
    /**
     * Returns links whose local syncable row has been deleted. The push
     * service uses this list to issue tombstone deletes on Google's side.
     *
     * @return Collection<int, GoogleCalendarLink>
     */
    public function execute(GoogleCalendarConnection $connection): Collection
    {
        $eventOrphans = GoogleCalendarLink::query()
            ->where('connection_id', $connection->id)
            ->where('syncable_type', Event::class)
            ->whereNotIn('syncable_id', function ($q) {
                $q->select('id')->from('calendar_events');
            })
            ->get();

        $taskOrphans = GoogleCalendarLink::query()
            ->where('connection_id', $connection->id)
            ->where('syncable_type', Task::class)
            ->whereNotIn('syncable_id', function ($q) {
                $q->select('id')->from('tasks');
            })
            ->get();

        return $eventOrphans->concat($taskOrphans);
    }
}
