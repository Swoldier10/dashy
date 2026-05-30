<?php

namespace Tests\Unit\Domains\TimeTracking\Actions;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\TimeTracking\Actions\ListActiveTimersForUserTeamsAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListActiveTimersForUserTeamsActionTest extends TestCase
{
    use RefreshDatabase;

    private function taskInTeam(Team $team): Task
    {
        return Task::factory()->create(['project_id' => Project::factory()->create(['team_id' => $team->id])->id]);
    }

    public function test_returns_running_timers_for_teammates_only(): void
    {
        $actor = User::factory()->create();
        $teammate = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach([
            $actor->id => ['role' => TeamRole::Member->value],
            $teammate->id => ['role' => TeamRole::Member->value],
        ]);
        $running = TimeEntry::factory()->running()->forUser($teammate)->forTask($this->taskInTeam($team))->create();

        // A stopped timer in the same team must not appear.
        TimeEntry::factory()->forUser($teammate)->forTask($this->taskInTeam($team))->create();

        // A running timer in a team the actor is NOT a member of must not appear.
        $otherTeam = Team::factory()->create();
        $stranger = User::factory()->create();
        $otherTeam->members()->attach($stranger->id, ['role' => TeamRole::Member->value]);
        TimeEntry::factory()->running()->forUser($stranger)->forTask($this->taskInTeam($otherTeam))->create();

        $active = (new ListActiveTimersForUserTeamsAction)->execute($actor);

        $this->assertSame([$running->id], $active->pluck('id')->all());
        $this->assertTrue($active->first()->relationLoaded('user'));
        $this->assertTrue($active->first()->relationLoaded('task'));
    }

    public function test_returns_empty_when_nobody_is_running_a_timer(): void
    {
        $actor = User::factory()->create();
        Team::factory()->create()->members()->attach($actor->id, ['role' => TeamRole::Member->value]);

        $this->assertTrue((new ListActiveTimersForUserTeamsAction)->execute($actor)->isEmpty());
    }
}
