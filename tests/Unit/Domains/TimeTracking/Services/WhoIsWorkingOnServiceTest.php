<?php

namespace Tests\Unit\Domains\TimeTracking\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Domains\TimeTracking\Services\WhoIsWorkingOnService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhoIsWorkingOnServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_running_timers_within_the_actors_team_graph(): void
    {
        $actor = User::factory()->create();
        $teammate = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach([
            $actor->id => ['role' => TeamRole::Member->value],
            $teammate->id => ['role' => TeamRole::Member->value],
        ]);
        $task = Task::factory()->create(['project_id' => Project::factory()->create(['team_id' => $team->id])->id]);
        $running = TimeEntry::factory()->running()->forUser($teammate)->forTask($task)->create();

        // A running timer outside the actor's teams must be hidden.
        $otherTeam = Team::factory()->create();
        $stranger = User::factory()->create();
        $otherTeam->members()->attach($stranger->id, ['role' => TeamRole::Member->value]);
        $otherTask = Task::factory()->create(['project_id' => Project::factory()->create(['team_id' => $otherTeam->id])->id]);
        TimeEntry::factory()->running()->forUser($stranger)->forTask($otherTask)->create();

        $result = app(WhoIsWorkingOnService::class)->execute($actor);

        $this->assertSame([$running->id], $result->pluck('id')->all());
    }
}
