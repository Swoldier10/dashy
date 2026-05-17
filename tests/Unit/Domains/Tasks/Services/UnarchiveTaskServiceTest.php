<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\UnarchiveTaskService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnarchiveTaskServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_unarchive_task(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $task = Task::factory()->archived()->create(['project_id' => $project->id]);

        $updated = app(UnarchiveTaskService::class)->execute($user, $task->id);

        $this->assertFalse($updated->is_archived);
        $this->assertFalse($task->refresh()->is_archived);
    }

    public function test_non_member_cannot_unarchive_task(): void
    {
        $stranger = User::factory()->create();
        $task = Task::factory()->archived()->create();

        $this->expectException(AuthorizationException::class);

        app(UnarchiveTaskService::class)->execute($stranger, $task->id);
    }

    public function test_unarchiving_active_task_is_idempotent(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);

        $updated = app(UnarchiveTaskService::class)->execute($user, $task->id);

        $this->assertFalse($updated->is_archived);
    }
}
