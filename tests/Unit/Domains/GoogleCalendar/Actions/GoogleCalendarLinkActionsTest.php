<?php

namespace Tests\Unit\Domains\GoogleCalendar\Actions;

use App\Domains\Calendar\Models\Event;
use App\Domains\GoogleCalendar\Actions\DeleteGoogleCalendarLinkAction;
use App\Domains\GoogleCalendar\Actions\FindLinkByGoogleEventIdAction;
use App\Domains\GoogleCalendar\Actions\FindLinkForSyncableAction;
use App\Domains\GoogleCalendar\Actions\UpsertGoogleCalendarLinkAction;
use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Domains\GoogleCalendar\Models\GoogleCalendarLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleCalendarLinkActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_upsert_creates_then_updates_link(): void
    {
        $connection = GoogleCalendarConnection::factory()->create();
        $event = Event::factory()->forUser($connection->user)->create();

        $created = (new UpsertGoogleCalendarLinkAction)->execute(
            $connection, $event, 'gcal-event-1', 'etag-1', now(),
        );
        $this->assertSame('gcal-event-1', $created->google_event_id);

        $updated = (new UpsertGoogleCalendarLinkAction)->execute(
            $connection, $event, 'gcal-event-1', 'etag-2', now(),
        );
        $this->assertSame($created->id, $updated->id);
        $this->assertSame('etag-2', $updated->etag);
        $this->assertSame(1, GoogleCalendarLink::count());
    }

    public function test_find_by_google_event_id_scopes_to_connection(): void
    {
        $connectionA = GoogleCalendarConnection::factory()->create();
        $connectionB = GoogleCalendarConnection::factory()->create();
        $event = Event::factory()->forUser($connectionA->user)->create();

        (new UpsertGoogleCalendarLinkAction)->execute($connectionA, $event, 'shared-id', null, now());

        $this->assertNotNull((new FindLinkByGoogleEventIdAction)->execute($connectionA, 'shared-id'));
        $this->assertNull((new FindLinkByGoogleEventIdAction)->execute($connectionB, 'shared-id'));
    }

    public function test_find_for_syncable_returns_link(): void
    {
        $connection = GoogleCalendarConnection::factory()->create();
        $event = Event::factory()->forUser($connection->user)->create();
        (new UpsertGoogleCalendarLinkAction)->execute($connection, $event, 'g-1', null, now());

        $this->assertNotNull((new FindLinkForSyncableAction)->execute($connection, $event));
    }

    public function test_delete_removes_link(): void
    {
        $connection = GoogleCalendarConnection::factory()->create();
        $event = Event::factory()->forUser($connection->user)->create();
        $link = (new UpsertGoogleCalendarLinkAction)->execute($connection, $event, 'g-1', null, now());

        (new DeleteGoogleCalendarLinkAction)->execute($link);

        $this->assertDatabaseMissing('google_calendar_links', ['id' => $link->id]);
    }
}
