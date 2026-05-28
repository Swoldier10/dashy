<?php

namespace Tests\Unit\Domains\GoogleCalendar\Actions;

use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Models\Event;
use App\Domains\GoogleCalendar\Actions\ListDirtyEventsForUserAction;
use App\Domains\GoogleCalendar\Actions\UpsertGoogleCalendarLinkAction;
use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListDirtyEventsForUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_unlinked_future_events_for_the_user(): void
    {
        $connection = GoogleCalendarConnection::factory()->create();
        $event = Event::factory()->forUser($connection->user)->create([
            'start_at' => CarbonImmutable::now()->addDays(2),
            'end_at' => CarbonImmutable::now()->addDays(2)->addHour(),
        ]);

        $result = (new ListDirtyEventsForUserAction)->execute($connection);

        $this->assertCount(1, $result);
        $this->assertSame($event->id, $result->first()->id);
    }

    public function test_skips_events_owned_by_other_users(): void
    {
        $connection = GoogleCalendarConnection::factory()->create();
        Event::factory()->create([
            'start_at' => CarbonImmutable::now()->addDays(2),
            'end_at' => CarbonImmutable::now()->addDays(2)->addHour(),
        ]); // belongs to a different user via factory

        $this->assertCount(0, (new ListDirtyEventsForUserAction)->execute($connection));
    }

    public function test_skips_past_events(): void
    {
        $connection = GoogleCalendarConnection::factory()->create();
        Event::factory()->forUser($connection->user)->create([
            'start_at' => CarbonImmutable::now()->subDays(2),
            'end_at' => CarbonImmutable::now()->subDays(2)->addHour(),
        ]);

        $this->assertCount(0, (new ListDirtyEventsForUserAction)->execute($connection));
    }

    public function test_skips_recurring_events(): void
    {
        $connection = GoogleCalendarConnection::factory()->create();
        Event::factory()
            ->forUser($connection->user)
            ->recurring(RecurrenceFreq::Weekly)
            ->create([
                'start_at' => CarbonImmutable::now()->addDays(2),
                'end_at' => CarbonImmutable::now()->addDays(2)->addHour(),
            ]);

        $this->assertCount(0, (new ListDirtyEventsForUserAction)->execute($connection));
    }

    public function test_skips_already_synced_events(): void
    {
        $connection = GoogleCalendarConnection::factory()->create();
        $event = Event::factory()->forUser($connection->user)->create([
            'start_at' => CarbonImmutable::now()->addDays(2),
            'end_at' => CarbonImmutable::now()->addDays(2)->addHour(),
        ]);

        (new UpsertGoogleCalendarLinkAction)->execute(
            $connection, $event, 'g-event-1', 'etag', now()->addMinute()
        );

        $this->assertCount(0, (new ListDirtyEventsForUserAction)->execute($connection));
    }

    public function test_returns_event_when_updated_after_last_synced_at(): void
    {
        $connection = GoogleCalendarConnection::factory()->create();
        $event = Event::factory()->forUser($connection->user)->create([
            'start_at' => CarbonImmutable::now()->addDays(2),
            'end_at' => CarbonImmutable::now()->addDays(2)->addHour(),
        ]);

        (new UpsertGoogleCalendarLinkAction)->execute(
            $connection, $event, 'g-event-1', 'etag', now()->subHour(),
        );

        $event->touch();

        $this->assertCount(1, (new ListDirtyEventsForUserAction)->execute($connection));
    }
}
