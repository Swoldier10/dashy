<?php

namespace Tests\Unit\Domains\TimeTracking\Actions;

use App\Domains\TimeTracking\Actions\FindActiveTimerForUserAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindActiveTimerForUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_running_entry_for_user(): void
    {
        $user = User::factory()->create();
        TimeEntry::factory()->forUser($user)->create();
        $running = TimeEntry::factory()->forUser($user)->running()->create();

        $found = (new FindActiveTimerForUserAction)->execute($user);

        $this->assertNotNull($found);
        $this->assertSame($running->id, $found->id);
    }

    public function test_returns_null_when_no_running_entry(): void
    {
        $user = User::factory()->create();
        TimeEntry::factory()->forUser($user)->create();

        $this->assertNull((new FindActiveTimerForUserAction)->execute($user));
    }

    public function test_ignores_other_users_running_entries(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        TimeEntry::factory()->forUser($other)->running()->create();

        $this->assertNull((new FindActiveTimerForUserAction)->execute($user));
    }
}
