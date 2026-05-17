<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\UnassignTaskService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnassignTaskServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_unassigns_user(): void
    {
        $actor = User::factory()->create();
        $assignee = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach([
            $actor->id => ['role' => TeamRole::Member->value],
            $assignee->id => ['role' => TeamRole::Member->value],
        ]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);
        $task->assignees()->attach($assignee->id);

        app(UnassignTaskService::class)->execute($actor, $task->id, $assignee->id);

        $this->assertFalse($task->refresh()->assignees->contains('id', $assignee->id));
    }
}
