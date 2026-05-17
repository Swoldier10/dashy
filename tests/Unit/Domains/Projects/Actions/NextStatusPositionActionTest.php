<?php

namespace Tests\Unit\Domains\Projects\Actions;

use App\Domains\Projects\Actions\NextStatusPositionAction;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NextStatusPositionActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_zero_when_no_statuses(): void
    {
        $project = Project::factory()->create();

        $next = (new NextStatusPositionAction)->execute($project->id, ProjectStatusCategory::Active);

        $this->assertSame(0, $next);
    }

    public function test_returns_max_plus_one_in_category(): void
    {
        $project = Project::factory()->create();
        ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Active->value,
            'position' => 0,
        ]);
        ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Active->value,
            'position' => 4,
        ]);
        // Different category — should not affect.
        ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Done->value,
            'position' => 9,
        ]);

        $next = (new NextStatusPositionAction)->execute($project->id, ProjectStatusCategory::Active);

        $this->assertSame(5, $next);
    }

    public function test_scoped_per_project(): void
    {
        $a = Project::factory()->create();
        $b = Project::factory()->create();
        ProjectStatus::factory()->create([
            'project_id' => $a->id,
            'category' => ProjectStatusCategory::Active->value,
            'position' => 7,
        ]);

        $next = (new NextStatusPositionAction)->execute($b->id, ProjectStatusCategory::Active);

        $this->assertSame(0, $next);
    }
}
