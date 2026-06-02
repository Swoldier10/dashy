<?php

namespace Tests\Unit\Domains\Calendar\Actions;

use App\Domains\Calendar\Actions\ListEventsStartingWithinAction;
use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Models\Event;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListEventsStartingWithinActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_candidate_series_across_users_for_the_window(): void
    {
        $from = CarbonImmutable::parse('2026-06-02 12:00:00');
        $to = $from->addMinutes(30);

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $inWindow = Event::factory()->forUser($userA)->create([
            'start_at' => '2026-06-02 12:10:00',
            'end_at' => '2026-06-02 13:00:00',
        ]);
        $otherOwnerRecurring = Event::factory()->forUser($userB)->recurring(RecurrenceFreq::Daily)->create([
            'start_at' => '2026-05-01 12:15:00',
            'end_at' => '2026-05-01 12:45:00',
        ]);
        Event::factory()->forUser($userA)->create([
            'start_at' => '2026-06-02 15:00:00',
            'end_at' => '2026-06-02 16:00:00',
        ]);

        $result = (new ListEventsStartingWithinAction)->execute($from, $to);

        $this->assertEqualsCanonicalizing(
            [$inWindow->id, $otherOwnerRecurring->id],
            $result->pluck('id')->all(),
        );
    }

    public function test_excludes_recurring_series_that_ended_before_the_window(): void
    {
        $from = CarbonImmutable::parse('2026-06-02 12:00:00');

        Event::factory()->forUser(User::factory()->create())
            ->recurring(RecurrenceFreq::Daily, CarbonImmutable::parse('2026-05-20'))
            ->create([
                'start_at' => '2026-05-01 12:15:00',
                'end_at' => '2026-05-01 12:45:00',
            ]);

        $result = (new ListEventsStartingWithinAction)->execute($from, $from->addMinutes(30));

        $this->assertCount(0, $result);
    }
}
