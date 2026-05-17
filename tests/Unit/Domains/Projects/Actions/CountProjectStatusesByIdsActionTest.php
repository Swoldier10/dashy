<?php

namespace Tests\Unit\Domains\Projects\Actions;

use App\Domains\Projects\Actions\CountProjectStatusesByIdsAction;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountProjectStatusesByIdsActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_counts_only_matching_project_and_category(): void
    {
        $project = Project::factory()->create();
        $other = Project::factory()->create();

        $a = ProjectStatus::factory()->create([
            'project_id' => $project->id, 'category' => ProjectStatusCategory::Active->value,
        ]);
        $b = ProjectStatus::factory()->create([
            'project_id' => $project->id, 'category' => ProjectStatusCategory::Active->value,
        ]);
        $wrongCategory = ProjectStatus::factory()->create([
            'project_id' => $project->id, 'category' => ProjectStatusCategory::Done->value,
        ]);
        $wrongProject = ProjectStatus::factory()->create([
            'project_id' => $other->id, 'category' => ProjectStatusCategory::Active->value,
        ]);

        $count = (new CountProjectStatusesByIdsAction)->execute(
            $project->id,
            ProjectStatusCategory::Active,
            [$a->id, $b->id, $wrongCategory->id, $wrongProject->id],
        );

        $this->assertSame(2, $count);
    }

    public function test_returns_zero_for_empty_array(): void
    {
        $project = Project::factory()->create();

        $count = (new CountProjectStatusesByIdsAction)->execute(
            $project->id,
            ProjectStatusCategory::Active,
            [],
        );

        $this->assertSame(0, $count);
    }
}
