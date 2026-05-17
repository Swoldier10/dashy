<?php

namespace Tests\Unit\Domains\Projects\Actions;

use App\Domains\Projects\Actions\ReorderProjectStatusesAction;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReorderProjectStatusesActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_writes_positions_in_given_order(): void
    {
        $project = Project::factory()->create();
        $a = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Active->value,
            'position' => 0,
            'name' => 'A',
        ]);
        $b = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Active->value,
            'position' => 1,
            'name' => 'B',
        ]);
        $c = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Active->value,
            'position' => 2,
            'name' => 'C',
        ]);

        (new ReorderProjectStatusesAction)->execute(
            $project->id,
            ProjectStatusCategory::Active,
            [$c->id, $a->id, $b->id],
        );

        $this->assertSame(0, $c->fresh()->position);
        $this->assertSame(1, $a->fresh()->position);
        $this->assertSame(2, $b->fresh()->position);
    }

    public function test_does_not_touch_rows_in_other_projects_or_categories(): void
    {
        $project = Project::factory()->create();
        $other = Project::factory()->create();

        $mine = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Active->value,
            'position' => 5,
        ]);
        $theirs = ProjectStatus::factory()->create([
            'project_id' => $other->id,
            'category' => ProjectStatusCategory::Active->value,
            'position' => 5,
        ]);

        (new ReorderProjectStatusesAction)->execute(
            $project->id,
            ProjectStatusCategory::Active,
            [$mine->id, $theirs->id],
        );

        $this->assertSame(0, $mine->fresh()->position);
        $this->assertSame(5, $theirs->fresh()->position, 'Other project rows must be untouched.');
    }
}
