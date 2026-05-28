<?php

namespace Tests\Unit\Domains\GoogleCalendar\Services;

use App\Domains\Calendar\Models\Event;
use App\Domains\GoogleCalendar\Actions\UpsertGoogleCalendarLinkAction;
use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Domains\GoogleCalendar\Models\GoogleCalendarLink;
use App\Domains\GoogleCalendar\Services\SyncGoogleCalendarService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncGoogleCalendarServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_pull_creates_local_event_from_new_google_event(): void
    {
        $connection = GoogleCalendarConnection::factory()->create();

        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary/events*' => Http::response([
                'items' => [[
                    'id' => 'g-1',
                    'etag' => '"etag-1"',
                    'status' => 'confirmed',
                    'summary' => 'From Google',
                    'description' => 'pulled in',
                    'start' => ['dateTime' => '2026-06-01T10:00:00+02:00'],
                    'end' => ['dateTime' => '2026-06-01T11:00:00+02:00'],
                    'updated' => '2026-05-25T12:00:00Z',
                ]],
                'nextSyncToken' => 'sync-token-1',
            ], 200),
        ]);

        app(SyncGoogleCalendarService::class)->execute($connection->user);

        $this->assertSame(1, Event::query()->where('user_id', $connection->user_id)->count());
        $this->assertSame(1, GoogleCalendarLink::query()->count());
        $connection->refresh();
        $this->assertSame('sync-token-1', $connection->sync_token);
        $this->assertNotNull($connection->last_synced_at);
    }

    public function test_pull_skips_recurring_events(): void
    {
        $connection = GoogleCalendarConnection::factory()->create();

        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary/events*' => Http::response([
                'items' => [[
                    'id' => 'g-rec',
                    'etag' => '"e"',
                    'status' => 'confirmed',
                    'summary' => 'Weekly meeting',
                    'start' => ['dateTime' => '2026-06-01T10:00:00+02:00'],
                    'end' => ['dateTime' => '2026-06-01T11:00:00+02:00'],
                    'recurrence' => ['RRULE:FREQ=WEEKLY'],
                    'updated' => '2026-05-25T12:00:00Z',
                ]],
                'nextSyncToken' => 'sync-token-1',
            ], 200),
        ]);

        app(SyncGoogleCalendarService::class)->execute($connection->user);

        $this->assertSame(0, Event::query()->count());
        $this->assertSame(0, GoogleCalendarLink::query()->count());
    }

    public function test_pull_deletes_local_event_on_cancelled_status(): void
    {
        $connection = GoogleCalendarConnection::factory()->create();
        $event = Event::factory()->forUser($connection->user)->create();
        (new UpsertGoogleCalendarLinkAction)->execute($connection, $event, 'g-bye', '"old"', now()->subDay());

        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary/events*' => Http::response([
                'items' => [[
                    'id' => 'g-bye',
                    'status' => 'cancelled',
                ]],
                'nextSyncToken' => 'next',
            ], 200),
        ]);

        app(SyncGoogleCalendarService::class)->execute($connection->user);

        $this->assertSame(0, Event::query()->count());
        $this->assertSame(0, GoogleCalendarLink::query()->count());
    }

    public function test_410_gone_clears_sync_token_and_refetches(): void
    {
        $connection = GoogleCalendarConnection::factory()->create([
            'sync_token' => 'stale-token',
        ]);

        Http::fakeSequence('https://www.googleapis.com/calendar/v3/calendars/primary/events*')
            ->push(['error' => 'sync token expired'], 410)
            ->push([
                'items' => [[
                    'id' => 'g-fresh',
                    'etag' => '"e"',
                    'status' => 'confirmed',
                    'summary' => 'Fresh',
                    'start' => ['dateTime' => '2026-06-01T10:00:00+02:00'],
                    'end' => ['dateTime' => '2026-06-01T11:00:00+02:00'],
                    'updated' => '2026-05-25T12:00:00Z',
                ]],
                'nextSyncToken' => 'fresh-token',
            ], 200);

        app(SyncGoogleCalendarService::class)->execute($connection->user);

        $connection->refresh();
        $this->assertSame('fresh-token', $connection->sync_token);
        $this->assertSame(1, Event::query()->count());
    }

    public function test_push_inserts_unlinked_future_event_to_google(): void
    {
        $connection = GoogleCalendarConnection::factory()->create();
        Event::factory()->forUser($connection->user)->create([
            'title' => 'New local event',
            'start_at' => CarbonImmutable::now()->addDays(2),
            'end_at' => CarbonImmutable::now()->addDays(2)->addHour(),
        ]);

        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary/events?*' => Http::response([
                'items' => [],
                'nextSyncToken' => 's-token',
            ], 200),
            'https://www.googleapis.com/calendar/v3/calendars/primary/events' => Http::response([
                'id' => 'g-new',
                'etag' => '"e-new"',
            ], 200),
        ]);

        app(SyncGoogleCalendarService::class)->execute($connection->user);

        $this->assertSame(1, GoogleCalendarLink::query()->where('google_event_id', 'g-new')->count());
    }

    public function test_412_etag_conflict_skips_without_error(): void
    {
        $connection = GoogleCalendarConnection::factory()->create();
        $event = Event::factory()->forUser($connection->user)->create([
            'start_at' => CarbonImmutable::now()->addDays(2),
            'end_at' => CarbonImmutable::now()->addDays(2)->addHour(),
        ]);
        $link = (new UpsertGoogleCalendarLinkAction)->execute(
            $connection, $event, 'g-existing', '"old-etag"', now()->subDay(),
        );

        // Touch the event so it shows up as dirty.
        $event->touch();

        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary/events?*' => Http::response([
                'items' => [],
                'nextSyncToken' => 's',
            ], 200),
            'https://www.googleapis.com/calendar/v3/calendars/primary/events/g-existing' => Http::response([], 412),
        ]);

        $outcome = app(SyncGoogleCalendarService::class)->execute($connection->user);

        $this->assertSame(1, $outcome->skipped);
        // Link unchanged
        $this->assertSame('"old-etag"', $link->fresh()->etag);
    }

    public function test_orphan_link_triggers_remote_delete(): void
    {
        $connection = GoogleCalendarConnection::factory()->create();
        $event = Event::factory()->forUser($connection->user)->create();
        (new UpsertGoogleCalendarLinkAction)->execute($connection, $event, 'g-orphan', '"e"', now());
        $event->delete();

        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary/events?*' => Http::response([
                'items' => [],
                'nextSyncToken' => 's',
            ], 200),
            'https://www.googleapis.com/calendar/v3/calendars/primary/events/g-orphan' => Http::response([], 204),
        ]);

        $outcome = app(SyncGoogleCalendarService::class)->execute($connection->user);

        $this->assertSame(1, $outcome->deletedRemote);
        $this->assertSame(0, GoogleCalendarLink::query()->count());
    }
}
