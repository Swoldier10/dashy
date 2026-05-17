<?php

namespace Tests\Unit\Domains\Projects\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Projects\Services\RenameProjectStatusService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RenameProjectStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_rename(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id, 'name' => 'OLD']);

        $renamed = app(RenameProjectStatusService::class)->execute($owner, $status->id, 'NEW');

        $this->assertSame('NEW', $renamed->name);
    }

    public function test_member_cannot_rename(): void
    {
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id, 'name' => 'STAY']);

        $this->expectException(AuthorizationException::class);

        try {
            app(RenameProjectStatusService::class)->execute($member, $status->id, 'CHANGE');
        } finally {
            $this->assertSame('STAY', $status->fresh()->name);
        }
    }

    public function test_404_when_missing(): void
    {
        $user = User::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        app(RenameProjectStatusService::class)->execute($user, 99999, 'X');
    }

    public function test_validation_empty_name(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);

        $this->expectException(ValidationException::class);

        app(RenameProjectStatusService::class)->execute($owner, $status->id, '');
    }
}
