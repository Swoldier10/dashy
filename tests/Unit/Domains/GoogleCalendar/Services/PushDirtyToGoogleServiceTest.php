<?php

namespace Tests\Unit\Domains\GoogleCalendar\Services;

use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Models\Event;
use App\Domains\GoogleCalendar\DTOs\SyncOutcome;
use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Domains\GoogleCalendar\Models\GoogleCalendarLink;
use App\Domains\GoogleCalendar\Services\PushDirtyToGoogleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PushDirtyToGoogleServiceTest extends TestCase
{
    use RefreshDatabase;

    private function dirtyEvent(GoogleCalendarConnection $connection): Event
    {
        return Event::factory()->create([
            'user_id' => $connection->user_id,
            'start_at' => Carbon::now()->addDay(),
            'end_at' => Carbon::now()->addDay()->addHour(),
            'recurrence_freq' => RecurrenceFreq::None->value,
        ]);
    }

    public function test_pushes_a_dirty_local_event_and_records_the_link(): void
    {
        $connection = GoogleCalendarConnection::factory()->create();
        $this->dirtyEvent($connection);
        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary/events*' => Http::response(['id' => 'g-new', 'etag' => '"e1"'], 200),
        ]);

        $outcome = new SyncOutcome;
        app(PushDirtyToGoogleService::class)->execute($connection, $outcome);

        $this->assertSame(1, $outcome->pushed);
        $this->assertDatabaseHas('google_calendar_links', [
            'connection_id' => $connection->id,
            'google_event_id' => 'g-new',
        ]);
    }

    public function test_surrenders_on_an_etag_conflict_without_writing_a_link(): void
    {
        $connection = GoogleCalendarConnection::factory()->create();
        $this->dirtyEvent($connection);
        // 412 Precondition Failed = etag mismatch; Google wins, we skip.
        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary/events*' => Http::response('', 412),
        ]);

        $outcome = new SyncOutcome;
        app(PushDirtyToGoogleService::class)->execute($connection, $outcome);

        $this->assertSame(1, $outcome->skipped);
        $this->assertSame(0, GoogleCalendarLink::query()->count());
    }
}
