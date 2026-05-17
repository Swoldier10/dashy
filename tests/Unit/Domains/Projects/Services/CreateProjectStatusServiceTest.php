<?php

namespace Tests\Unit\Domains\Projects\Services;

use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Projects\Services\CreateProjectStatusService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateProjectStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    private function ownerProject(): array
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        return [$owner, $project];
    }

    public function test_owner_can_add_status(): void
    {
        [$owner, $project] = $this->ownerProject();

        $status = app(CreateProjectStatusService::class)->execute(
            $owner,
            $project->id,
            ProjectStatusCategory::Active,
            'IN PROGRESS',
        );

        $this->assertSame('IN PROGRESS', $status->name);
        $this->assertSame(ProjectStatusCategory::Active, $status->category);
        $this->assertSame(0, $status->position);
    }

    public function test_position_is_max_plus_one_per_category(): void
    {
        [$owner, $project] = $this->ownerProject();

        ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Active->value,
            'position' => 0,
        ]);
        ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Active->value,
            'position' => 1,
        ]);

        $status = app(CreateProjectStatusService::class)->execute(
            $owner,
            $project->id,
            ProjectStatusCategory::Active,
            'NEXT',
        );

        $this->assertSame(2, $status->position);
    }

    public function test_member_cannot_add(): void
    {
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        $this->expectException(AuthorizationException::class);

        app(CreateProjectStatusService::class)->execute(
            $member, $project->id, ProjectStatusCategory::Active, 'X',
        );
    }

    public function test_name_required(): void
    {
        [$owner, $project] = $this->ownerProject();

        $this->expectException(ValidationException::class);

        app(CreateProjectStatusService::class)->execute(
            $owner, $project->id, ProjectStatusCategory::Active, '',
        );
    }

    public function test_name_max_60(): void
    {
        [$owner, $project] = $this->ownerProject();

        $this->expectException(ValidationException::class);

        app(CreateProjectStatusService::class)->execute(
            $owner, $project->id, ProjectStatusCategory::Active, str_repeat('a', 61),
        );
    }
}
