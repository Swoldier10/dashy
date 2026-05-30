<?php

namespace Tests\Unit\Domains\TimeTracking\Services;

use App\Domains\TimeTracking\Models\TimeEntry;
use App\Domains\TimeTracking\Services\ListRecentTimeEntriesForUserService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ListRecentTimeEntriesForUserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_the_users_recent_entries_newest_first(): void
    {
        $user = User::factory()->create();
        $older = TimeEntry::factory()->forUser($user)->create(['started_at' => Carbon::now()->subDays(2)]);
        $newer = TimeEntry::factory()->forUser($user)->create(['started_at' => Carbon::now()->subHour()]);

        $entries = app(ListRecentTimeEntriesForUserService::class)->execute($user);

        $this->assertSame([$newer->id, $older->id], $entries->pluck('id')->all());
    }

    public function test_caps_the_result_to_the_given_limit(): void
    {
        $user = User::factory()->create();
        TimeEntry::factory()->count(3)->forUser($user)->create();

        $this->assertCount(2, app(ListRecentTimeEntriesForUserService::class)->execute($user, 2));
    }
}
