<?php

namespace Tests\Unit\Domains\Projects\Actions;

use App\Domains\Projects\Actions\CreateProjectStatusAction;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateProjectStatusActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_status_row(): void
    {
        $project = Project::factory()->create();

        $status = (new CreateProjectStatusAction)->execute([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Active->value,
            'name' => 'IN PROGRESS',
            'position' => 0,
        ]);

        $this->assertSame('IN PROGRESS', $status->name);
        $this->assertSame(ProjectStatusCategory::Active, $status->category);
        $this->assertSame(0, $status->position);
        $this->assertSame($project->id, $status->project_id);
    }
}
