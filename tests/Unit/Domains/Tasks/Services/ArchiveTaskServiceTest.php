<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\ArchiveTaskService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveTaskServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_archive_task(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);

        $updated = app(ArchiveTaskService::class)->execute($user, $task->id);

        $this->assertTrue($updated->is_archived);
        $this->assertTrue($task->refresh()->is_archived);
    }

    public function test_non_member_cannot_archive_task(): void
    {
        $stranger = User::factory()->create();
        $task = Task::factory()->create();

        $this->expectException(AuthorizationException::class);

        app(ArchiveTaskService::class)->execute($stranger, $task->id);
    }

    public function test_archiving_already_archived_task_is_idempotent(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $task = Task::factory()->archived()->create(['project_id' => $project->id]);

        $updated = app(ArchiveTaskService::class)->execute($user, $task->id);

        $this->assertTrue($updated->is_archived);
    }
}
