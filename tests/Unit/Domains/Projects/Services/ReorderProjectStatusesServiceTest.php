<?php

namespace Tests\Unit\Domains\Projects\Services;

use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Projects\Services\ReorderProjectStatusesService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReorderProjectStatusesServiceTest extends TestCase
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

    public function test_owner_can_reorder(): void
    {
        [$owner, $project] = $this->ownerProject();
        $a = ProjectStatus::factory()->create([
            'project_id' => $project->id, 'category' => ProjectStatusCategory::Active->value, 'position' => 0,
        ]);
        $b = ProjectStatus::factory()->create([
            'project_id' => $project->id, 'category' => ProjectStatusCategory::Active->value, 'position' => 1,
        ]);
        $c = ProjectStatus::factory()->create([
            'project_id' => $project->id, 'category' => ProjectStatusCategory::Active->value, 'position' => 2,
        ]);

        app(ReorderProjectStatusesService::class)->execute(
            $owner, $project->id, ProjectStatusCategory::Active, [$c->id, $a->id, $b->id],
        );

        $this->assertSame(0, $c->fresh()->position);
        $this->assertSame(1, $a->fresh()->position);
        $this->assertSame(2, $b->fresh()->position);
    }

    public function test_member_cannot_reorder(): void
    {
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create([
            'project_id' => $project->id, 'category' => ProjectStatusCategory::Active->value, 'position' => 0,
        ]);

        $this->expectException(AuthorizationException::class);

        try {
            app(ReorderProjectStatusesService::class)->execute(
                $member, $project->id, ProjectStatusCategory::Active, [$status->id],
            );
        } finally {
            $this->assertSame(0, $status->fresh()->position);
        }
    }

    public function test_validation_when_id_does_not_belong_to_project(): void
    {
        [$owner, $project] = $this->ownerProject();
        $foreign = Project::factory()->create();
        $alien = ProjectStatus::factory()->create([
            'project_id' => $foreign->id, 'category' => ProjectStatusCategory::Active->value,
        ]);

        $this->expectException(ValidationException::class);

        app(ReorderProjectStatusesService::class)->execute(
            $owner, $project->id, ProjectStatusCategory::Active, [$alien->id],
        );
    }

    public function test_validation_when_category_mismatches(): void
    {
        [$owner, $project] = $this->ownerProject();
        $wrongCategory = ProjectStatus::factory()->create([
            'project_id' => $project->id, 'category' => ProjectStatusCategory::Done->value,
        ]);

        $this->expectException(ValidationException::class);

        app(ReorderProjectStatusesService::class)->execute(
            $owner, $project->id, ProjectStatusCategory::Active, [$wrongCategory->id],
        );
    }

    public function test_validation_on_empty_array(): void
    {
        [$owner, $project] = $this->ownerProject();

        $this->expectException(ValidationException::class);

        app(ReorderProjectStatusesService::class)->execute(
            $owner, $project->id, ProjectStatusCategory::Active, [],
        );
    }
}
