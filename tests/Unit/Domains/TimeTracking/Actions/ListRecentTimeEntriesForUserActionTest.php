<?php

namespace Tests\Unit\Domains\TimeTracking\Actions;

use App\Domains\TimeTracking\Actions\ListRecentTimeEntriesForUserAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ListRecentTimeEntriesForUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_only_the_users_entries_newest_first(): void
    {
        $user = User::factory()->create();
        $older = TimeEntry::factory()->forUser($user)->create(['started_at' => Carbon::now()->subDays(2)]);
        $newer = TimeEntry::factory()->forUser($user)->create(['started_at' => Carbon::now()->subHour()]);
        TimeEntry::factory()->forUser(User::factory()->create())->create();

        $entries = (new ListRecentTimeEntriesForUserAction)->execute($user);

        $this->assertSame([$newer->id, $older->id], $entries->pluck('id')->all());
    }

    public function test_respects_and_clamps_the_limit(): void
    {
        $user = User::factory()->create();
        TimeEntry::factory()->count(3)->forUser($user)->create();

        $this->assertCount(2, (new ListRecentTimeEntriesForUserAction)->execute($user, 2));
        // Clamp: a 0/negative limit is forced to at least 1.
        $this->assertCount(1, (new ListRecentTimeEntriesForUserAction)->execute($user, 0));
    }
}
