<?php

namespace App\Domains\GoogleCalendar\Actions;

use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Domains\GoogleCalendar\Models\GoogleCalendarLink;

class FindLinkByGoogleEventIdAction
{
    public function execute(GoogleCalendarConnection $connection, string $googleEventId): ?GoogleCalendarLink
    {
        return GoogleCalendarLink::query()
            ->where('connection_id', $connection->id)
            ->where('google_event_id', $googleEventId)
            ->first();
    }
}
