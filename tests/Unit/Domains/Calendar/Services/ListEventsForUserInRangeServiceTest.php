<?php

namespace Tests\Unit\Domains\Calendar\Services;

use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Models\Event;
use App\Domains\Calendar\Services\ListEventsForUserInRangeService;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListEventsForUserInRangeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_expands_weekly_series_across_a_four_week_window(): void
    {
        $user = User::factory()->create();
        // Monday 2026-06-01 → 4 Mondays in June.
        Event::factory()->forUser($user)->create([
            'title' => 'Mon standup',
            'start_at' => '2026-06-01 09:00:00',
            'end_at' => '2026-06-01 09:30:00',
            'recurrence_freq' => RecurrenceFreq::Weekly->value,
        ]);

        $occs = app(ListEventsForUserInRangeService::class)->execute(
            $user,
            CarbonImmutable::parse('2026-06-01 00:00:00'),
            CarbonImmutable::parse('2026-06-28 23:59:59'),
        );

        $this->assertCount(4, $occs);
        $this->assertSame('2026-06-01', $occs[0]->startAt->toDateString());
        $this->assertSame('2026-06-22', $occs[3]->startAt->toDateString());
    }

    public function test_non_recurring_event_appears_once(): void
    {
        $user = User::factory()->create();
        Event::factory()->forUser($user)->create([
            'start_at' => '2026-06-15 09:00:00',
            'end_at' => '2026-06-15 10:00:00',
        ]);

        $occs = app(ListEventsForUserInRangeService::class)->execute(
            $user,
            CarbonImmutable::parse('2026-06-01 00:00:00'),
            CarbonImmutable::parse('2026-06-28 23:59:59'),
        );

        $this->assertCount(1, $occs);
    }

    public function test_sorted_by_start(): void
    {
        $user = User::factory()->create();
        Event::factory()->forUser($user)->create([
            'title' => 'Later',
            'start_at' => '2026-06-15 14:00:00',
            'end_at' => '2026-06-15 15:00:00',
        ]);
        Event::factory()->forUser($user)->create([
            'title' => 'Earlier',
            'start_at' => '2026-06-15 09:00:00',
            'end_at' => '2026-06-15 10:00:00',
        ]);

        $occs = app(ListEventsForUserInRangeService::class)->execute(
            $user,
            CarbonImmutable::parse('2026-06-15 00:00:00'),
            CarbonImmutable::parse('2026-06-15 23:59:59'),
        );

        $this->assertCount(2, $occs);
        $this->assertSame('Earlier', $occs[0]->event->title);
        $this->assertSame('Later', $occs[1]->event->title);
    }

    public function test_recurrence_until_caps_series(): void
    {
        $user = User::factory()->create();
        Event::factory()->forUser($user)->create([
            'start_at' => '2026-06-01 09:00:00',
            'end_at' => '2026-06-01 09:30:00',
            'recurrence_freq' => RecurrenceFreq::Daily->value,
            'recurrence_until' => '2026-06-03',
        ]);

        $occs = app(ListEventsForUserInRangeService::class)->execute(
            $user,
            CarbonImmutable::parse('2026-06-01 00:00:00'),
            CarbonImmutable::parse('2026-06-30 23:59:59'),
        );

        $this->assertCount(3, $occs);
    }
}
