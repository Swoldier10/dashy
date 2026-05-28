<?php

namespace App\Domains\GoogleCalendar\Actions;

use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Models\Event;
use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use Illuminate\Database\Eloquent\Collection;

class ListDirtyEventsForUserAction
{
    /**
     * @return Collection<int, Event>
     */
    public function execute(GoogleCalendarConnection $connection): Collection
    {
        return Event::query()
            ->select('calendar_events.*')
            ->leftJoin('google_calendar_links', function ($join) use ($connection) {
                $join->on('google_calendar_links.syncable_id', '=', 'calendar_events.id')
                    ->where('google_calendar_links.syncable_type', '=', Event::class)
                    ->where('google_calendar_links.connection_id', '=', $connection->id);
            })
            ->where('calendar_events.user_id', $connection->user_id)
            ->where('calendar_events.recurrence_freq', RecurrenceFreq::None->value)
            ->where('calendar_events.start_at', '>=', now())
            ->where(function ($q) {
                $q->whereNull('google_calendar_links.id')
                    ->orWhereColumn('calendar_events.updated_at', '>', 'google_calendar_links.last_synced_at');
            })
            ->orderBy('calendar_events.start_at')
            ->get();
    }
}
