<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\ListAllTasksForUserService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListAllTasksForUserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_delegates_to_the_action_for_the_actor(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);

        $result = app(ListAllTasksForUserService::class)->execute($user);

        $this->assertSame([$task->id], $result->pluck('id')->all());
    }

    public function test_returns_empty_for_a_user_with_no_teams(): void
    {
        $user = User::factory()->create();

        $result = app(ListAllTasksForUserService::class)->execute($user);

        $this->assertTrue($result->isEmpty());
    }
}
