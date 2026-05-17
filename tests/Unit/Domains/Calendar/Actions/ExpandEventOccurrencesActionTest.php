<?php

namespace Tests\Unit\Domains\Calendar\Actions;

use App\Domains\Calendar\Actions\ExpandEventOccurrencesAction;
use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Models\Event;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpandEventOccurrencesActionTest extends TestCase
{
    use RefreshDatabase;

    private function makeEvent(array $overrides = []): Event
    {
        $user = User::factory()->create();

        return Event::factory()->forUser($user)->create(array_merge([
            'start_at' => '2026-06-01 09:00:00',
            'end_at' => '2026-06-01 10:00:00',
        ], $overrides));
    }

    public function test_non_recurring_event_yields_single_occurrence_when_overlapping(): void
    {
        $event = $this->makeEvent();

        $occs = (new ExpandEventOccurrencesAction)->execute(
            $event,
            CarbonImmutable::parse('2026-06-01 00:00:00'),
            CarbonImmutable::parse('2026-06-02 00:00:00'),
        );

        $this->assertCount(1, $occs);
        $this->assertSame('2026-06-01 09:00:00', $occs[0]->startAt->format('Y-m-d H:i:s'));
        $this->assertSame('2026-06-01 10:00:00', $occs[0]->endAt->format('Y-m-d H:i:s'));
    }

    public function test_non_recurring_event_outside_window_yields_nothing(): void
    {
        $event = $this->makeEvent();

        $occs = (new ExpandEventOccurrencesAction)->execute(
            $event,
            CarbonImmutable::parse('2026-07-01 00:00:00'),
            CarbonImmutable::parse('2026-07-08 00:00:00'),
        );

        $this->assertCount(0, $occs);
    }

    public function test_daily_emits_one_per_day_in_window(): void
    {
        $event = $this->makeEvent([
            'recurrence_freq' => RecurrenceFreq::Daily->value,
        ]);

        $occs = (new ExpandEventOccurrencesAction)->execute(
            $event,
            CarbonImmutable::parse('2026-06-01 00:00:00'),
            CarbonImmutable::parse('2026-06-07 23:59:59'),
        );

        $this->assertCount(7, $occs);
        $this->assertSame('2026-06-01', $occs[0]->startAt->toDateString());
        $this->assertSame('2026-06-07', $occs[6]->startAt->toDateString());
    }

    public function test_weekly_emits_on_same_weekday(): void
    {
        // Mon 2026-06-01.
        $event = $this->makeEvent([
            'start_at' => '2026-06-01 09:00:00',
            'end_at' => '2026-06-01 10:00:00',
            'recurrence_freq' => RecurrenceFreq::Weekly->value,
        ]);

        $occs = (new ExpandEventOccurrencesAction)->execute(
            $event,
            CarbonImmutable::parse('2026-06-01 00:00:00'),
            CarbonImmutable::parse('2026-06-30 23:59:59'),
        );

        $this->assertCount(5, $occs);
        foreach ($occs as $occ) {
            $this->assertSame('Monday', $occ->startAt->format('l'));
        }
    }

    public function test_monthly_on_the_31st_skips_short_months(): void
    {
        // 2026-01-31 → skip Feb (28), Apr (30), Jun (30), Sep (30), Nov (30).
        $event = $this->makeEvent([
            'start_at' => '2026-01-31 09:00:00',
            'end_at' => '2026-01-31 10:00:00',
            'recurrence_freq' => RecurrenceFreq::Monthly->value,
        ]);

        $occs = (new ExpandEventOccurrencesAction)->execute(
            $event,
            CarbonImmutable::parse('2026-01-01 00:00:00'),
            CarbonImmutable::parse('2026-12-31 23:59:59'),
        );

        $dates = array_map(fn ($o) => $o->startAt->toDateString(), $occs);

        $this->assertSame([
            '2026-01-31',
            '2026-03-31',
            '2026-05-31',
            '2026-07-31',
            '2026-08-31',
            '2026-10-31',
            '2026-12-31',
        ], $dates);
    }

    public function test_monthly_on_the_29th_in_february_skips_non_leap_years(): void
    {
        // 2024-02-29 is a leap day. Series should hit 2024, 2028, 2032 for
        // Feb but every other month always.
        $event = $this->makeEvent([
            'start_at' => '2024-02-29 09:00:00',
            'end_at' => '2024-02-29 10:00:00',
            'recurrence_freq' => RecurrenceFreq::Monthly->value,
        ]);

        // Six-month window starting Feb 2025 (non-leap).
        $occs = (new ExpandEventOccurrencesAction)->execute(
            $event,
            CarbonImmutable::parse('2025-02-01 00:00:00'),
            CarbonImmutable::parse('2025-07-31 23:59:59'),
        );

        $dates = array_map(fn ($o) => $o->startAt->toDateString(), $occs);

        // Feb 2025 should be skipped (only 28 days), but Mar/May/Jul (>= 29 days) present.
        $this->assertNotContains('2025-02-29', $dates);
        $this->assertNotContains('2025-02-28', $dates);
        $this->assertContains('2025-03-29', $dates);
        $this->assertContains('2025-05-29', $dates);
        $this->assertContains('2025-07-29', $dates);
    }

    public function test_yearly_on_feb_29_only_appears_in_leap_years(): void
    {
        $event = $this->makeEvent([
            'start_at' => '2024-02-29 09:00:00',
            'end_at' => '2024-02-29 10:00:00',
            'recurrence_freq' => RecurrenceFreq::Yearly->value,
        ]);

        // 8-year window starting 2024 should hit 2024 and 2028 only.
        $occs = (new ExpandEventOccurrencesAction)->execute(
            $event,
            CarbonImmutable::parse('2024-01-01 00:00:00'),
            CarbonImmutable::parse('2031-12-31 23:59:59'),
        );

        $dates = array_map(fn ($o) => $o->startAt->toDateString(), $occs);

        $this->assertSame(['2024-02-29', '2028-02-29'], $dates);
    }

    public function test_recurrence_until_is_inclusive(): void
    {
        $event = $this->makeEvent([
            'start_at' => '2026-06-01 09:00:00',
            'end_at' => '2026-06-01 10:00:00',
            'recurrence_freq' => RecurrenceFreq::Daily->value,
            'recurrence_until' => '2026-06-03',
        ]);

        $occs = (new ExpandEventOccurrencesAction)->execute(
            $event,
            CarbonImmutable::parse('2026-06-01 00:00:00'),
            CarbonImmutable::parse('2026-12-31 23:59:59'),
        );

        $dates = array_map(fn ($o) => $o->startAt->toDateString(), $occs);

        $this->assertSame(['2026-06-01', '2026-06-02', '2026-06-03'], $dates);
    }

    public function test_unbounded_series_respects_query_window_upper_bound(): void
    {
        $event = $this->makeEvent([
            'start_at' => '2026-01-01 09:00:00',
            'end_at' => '2026-01-01 10:00:00',
            'recurrence_freq' => RecurrenceFreq::Daily->value,
            'recurrence_until' => null,
        ]);

        // 30-day window — should never exceed 30 occurrences regardless of
        // unbounded-ness.
        $occs = (new ExpandEventOccurrencesAction)->execute(
            $event,
            CarbonImmutable::parse('2026-01-01 00:00:00'),
            CarbonImmutable::parse('2026-01-30 23:59:59'),
        );

        $this->assertCount(30, $occs);
    }

    public function test_duration_is_preserved_across_occurrences(): void
    {
        $event = $this->makeEvent([
            'start_at' => '2026-06-01 09:00:00',
            'end_at' => '2026-06-01 10:30:00',
            'recurrence_freq' => RecurrenceFreq::Weekly->value,
        ]);

        $occs = (new ExpandEventOccurrencesAction)->execute(
            $event,
            CarbonImmutable::parse('2026-06-01 00:00:00'),
            CarbonImmutable::parse('2026-06-30 23:59:59'),
        );

        foreach ($occs as $occ) {
            $this->assertSame(90 * 60, $occ->endAt->getTimestamp() - $occ->startAt->getTimestamp());
        }
    }
}
