<?php

namespace Tests\Unit\Domains\Projects\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Projects\Services\DeleteProjectStatusService;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DeleteProjectStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_delete(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);

        app(DeleteProjectStatusService::class)->execute($owner, $status->id);

        $this->assertSame(0, ProjectStatus::count());
    }

    public function test_member_cannot_delete(): void
    {
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);

        $this->expectException(AuthorizationException::class);

        try {
            app(DeleteProjectStatusService::class)->execute($member, $status->id);
        } finally {
            $this->assertSame(1, ProjectStatus::count());
        }
    }

    public function test_404_when_missing(): void
    {
        $user = User::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        app(DeleteProjectStatusService::class)->execute($user, 99999);
    }

    public function test_throws_when_status_has_tasks(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);
        Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id]);

        $this->expectException(ValidationException::class);

        try {
            app(DeleteProjectStatusService::class)->execute($owner, $status->id);
        } finally {
            $this->assertSame(1, ProjectStatus::count());
        }
    }
}
