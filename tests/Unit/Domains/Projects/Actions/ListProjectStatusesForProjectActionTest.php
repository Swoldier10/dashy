<?php

namespace Tests\Unit\Domains\Projects\Actions;

use App\Domains\Projects\Actions\ListProjectStatusesForProjectAction;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListProjectStatusesForProjectActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_only_this_projects_statuses_ordered(): void
    {
        $project = Project::factory()->create();
        $other = Project::factory()->create();

        ProjectStatus::factory()->create([
            'project_id' => $other->id, 'category' => ProjectStatusCategory::Active->value, 'position' => 0,
        ]);
        $second = ProjectStatus::factory()->create([
            'project_id' => $project->id, 'category' => ProjectStatusCategory::Active->value, 'position' => 1, 'name' => 'Second',
        ]);
        $first = ProjectStatus::factory()->create([
            'project_id' => $project->id, 'category' => ProjectStatusCategory::Active->value, 'position' => 0, 'name' => 'First',
        ]);

        $list = (new ListProjectStatusesForProjectAction)->execute($project);

        $names = $list->pluck('name')->all();
        $this->assertSame(['First', 'Second'], $names);
        $this->assertCount(2, $list);
    }
}
