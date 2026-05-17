<?php

namespace Tests\Unit\Domains\Calendar\Actions;

use App\Domains\Calendar\Actions\ListEventsForUserInRangeAction;
use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Models\Event;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListEventsForUserInRangeActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_only_owners_events_overlapping_window(): void
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();

        $inWindow = Event::factory()->forUser($user)->create([
            'start_at' => '2026-06-10 09:00:00',
            'end_at' => '2026-06-10 10:00:00',
        ]);

        Event::factory()->forUser($user)->create([
            'start_at' => '2026-07-01 09:00:00',
            'end_at' => '2026-07-01 10:00:00',
        ]);

        Event::factory()->forUser($stranger)->create([
            'start_at' => '2026-06-10 09:00:00',
            'end_at' => '2026-06-10 10:00:00',
        ]);

        $result = (new ListEventsForUserInRangeAction)->execute(
            $user,
            CarbonImmutable::parse('2026-06-08 00:00:00'),
            CarbonImmutable::parse('2026-06-14 23:59:59'),
        );

        $this->assertCount(1, $result);
        $this->assertSame($inWindow->id, $result->first()->id);
    }

    public function test_includes_recurring_series_anchored_before_window(): void
    {
        $user = User::factory()->create();

        $series = Event::factory()->forUser($user)->create([
            'start_at' => '2026-01-05 09:00:00',
            'end_at' => '2026-01-05 10:00:00',
            'recurrence_freq' => RecurrenceFreq::Weekly->value,
            'recurrence_until' => null,
        ]);

        $result = (new ListEventsForUserInRangeAction)->execute(
            $user,
            CarbonImmutable::parse('2026-06-08 00:00:00'),
            CarbonImmutable::parse('2026-06-14 23:59:59'),
        );

        $this->assertCount(1, $result);
        $this->assertSame($series->id, $result->first()->id);
    }

    public function test_excludes_series_whose_recurrence_until_is_before_window(): void
    {
        $user = User::factory()->create();

        Event::factory()->forUser($user)->create([
            'start_at' => '2026-01-05 09:00:00',
            'end_at' => '2026-01-05 10:00:00',
            'recurrence_freq' => RecurrenceFreq::Weekly->value,
            'recurrence_until' => '2026-03-01',
        ]);

        $result = (new ListEventsForUserInRangeAction)->execute(
            $user,
            CarbonImmutable::parse('2026-06-08 00:00:00'),
            CarbonImmutable::parse('2026-06-14 23:59:59'),
        );

        $this->assertCount(0, $result);
    }
}
