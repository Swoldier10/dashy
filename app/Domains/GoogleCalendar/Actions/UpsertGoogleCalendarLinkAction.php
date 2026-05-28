<?php

namespace App\Domains\GoogleCalendar\Actions;

use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Domains\GoogleCalendar\Models\GoogleCalendarLink;
use Illuminate\Database\Eloquent\Model;

class UpsertGoogleCalendarLinkAction
{
    public function execute(
        GoogleCalendarConnection $connection,
        Model $syncable,
        string $googleEventId,
        ?string $etag,
        \DateTimeInterface $lastSyncedAt,
    ): GoogleCalendarLink {
        return GoogleCalendarLink::query()->updateOrCreate(
            [
                'connection_id' => $connection->id,
                'syncable_type' => $syncable->getMorphClass(),
                'syncable_id' => $syncable->getKey(),
            ],
            [
                'google_event_id' => $googleEventId,
                'etag' => $etag,
                'last_synced_at' => $lastSyncedAt,
            ],
        );
    }
}
