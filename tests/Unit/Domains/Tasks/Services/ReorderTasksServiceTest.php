<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\ReorderTasksService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReorderTasksServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reorders_for_member(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);
        $a = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id, 'position' => 0]);
        $b = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id, 'position' => 1]);

        app(ReorderTasksService::class)->execute($user, $status->id, [$b->id, $a->id]);

        $this->assertSame(0, $b->refresh()->position);
        $this->assertSame(1, $a->refresh()->position);
    }

    public function test_non_member_cannot_reorder(): void
    {
        $stranger = User::factory()->create();
        $project = Project::factory()->create();
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);

        $this->expectException(AuthorizationException::class);

        app(ReorderTasksService::class)->execute($stranger, $status->id, []);
    }
}
