<?php

namespace Tests\Unit\Domains\GoogleCalendar\Services;

use App\Domains\Calendar\Models\Event;
use App\Domains\GoogleCalendar\Actions\UpsertGoogleCalendarLinkAction;
use App\Domains\GoogleCalendar\DTOs\SyncOutcome;
use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Domains\GoogleCalendar\Models\GoogleCalendarLink;
use App\Domains\GoogleCalendar\Services\PullEventsFromGoogleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class PullEventsFromGoogleServiceTest extends TestCase
{
    use RefreshDatabase;

    private function fakeOneNewEvent(): void
    {
        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary/events*' => Http::response([
                'items' => [[
                    'id' => 'g-1',
                    'etag' => '"etag-1"',
                    'status' => 'confirmed',
                    'summary' => 'From Google',
                    'start' => ['dateTime' => '2026-06-01T10:00:00+02:00'],
                    'end' => ['dateTime' => '2026-06-01T11:00:00+02:00'],
                    'updated' => '2026-05-25T12:00:00Z',
                ]],
                'nextSyncToken' => 'sync-token-1',
            ], 200),
        ]);
    }

    public function test_creates_the_local_event_and_link_for_a_new_google_event(): void
    {
        $connection = GoogleCalendarConnection::factory()->create();
        $this->fakeOneNewEvent();

        $outcome = new SyncOutcome;
        app(PullEventsFromGoogleService::class)->execute($connection, $outcome);

        $this->assertSame(1, Event::query()->where('user_id', $connection->user_id)->count());
        $this->assertSame(1, GoogleCalendarLink::query()->count());
        $this->assertSame(1, $outcome->pulled);
    }

    public function test_rolls_back_the_local_event_when_the_link_upsert_fails(): void
    {
        // Regression for the split-transaction bug: the syncable create and the
        // link upsert now share ONE transaction, so a failed upsert must leave
        // NO orphaned local Event behind.
        $connection = GoogleCalendarConnection::factory()->create();
        $this->fakeOneNewEvent();

        $throwing = Mockery::mock(UpsertGoogleCalendarLinkAction::class);
        $throwing->shouldReceive('execute')->andThrow(new RuntimeException('link write failed'));
        $this->app->instance(UpsertGoogleCalendarLinkAction::class, $throwing);

        try {
            app(PullEventsFromGoogleService::class)->execute($connection, new SyncOutcome);
            $this->fail('Expected the link-upsert failure to propagate.');
        } catch (RuntimeException) {
            // expected
        }

        $this->assertSame(0, Event::query()->count(), 'A failed link write must roll back the created Event.');
        $this->assertSame(0, GoogleCalendarLink::query()->count());
    }
}
