<?php

namespace App\Domains\GoogleCalendar\Actions;

use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Domains\GoogleCalendar\Models\GoogleCalendarLink;
use Illuminate\Database\Eloquent\Model;

class FindLinkForSyncableAction
{
    public function execute(GoogleCalendarConnection $connection, Model $syncable): ?GoogleCalendarLink
    {
        return GoogleCalendarLink::query()
            ->where('connection_id', $connection->id)
            ->where('syncable_type', $syncable->getMorphClass())
            ->where('syncable_id', $syncable->getKey())
            ->first();
    }
}
