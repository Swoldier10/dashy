<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Enums\TaskPriority;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\UpdateTaskPriorityService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateTaskPriorityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_priority(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $task = Task::factory()->create(['project_id' => $project->id, 'priority' => 'normal']);

        $updated = app(UpdateTaskPriorityService::class)->execute($user, $task->id, TaskPriority::Urgent->value);

        $this->assertSame('urgent', $updated->priority->value);
    }

    public function test_rejects_unknown_priority(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);

        $this->expectException(ValidationException::class);

        app(UpdateTaskPriorityService::class)->execute($user, $task->id, 'critical');
    }
}
