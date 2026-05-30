<?php

namespace Tests\Unit\Domains\Projects\Actions;

use App\Domains\Projects\Actions\IsProjectStatusInProjectAction;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IsProjectStatusInProjectActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_true_when_status_belongs_to_project(): void
    {
        $project = Project::factory()->create();
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);

        $result = (new IsProjectStatusInProjectAction)->execute($status->id, $project->id);

        $this->assertTrue($result);
    }

    public function test_returns_false_for_status_in_different_project(): void
    {
        $project = Project::factory()->create();
        $other = Project::factory()->create();
        $status = ProjectStatus::factory()->create(['project_id' => $other->id]);

        $result = (new IsProjectStatusInProjectAction)->execute($status->id, $project->id);

        $this->assertFalse($result);
    }

    public function test_returns_false_for_unknown_status(): void
    {
        $project = Project::factory()->create();

        $result = (new IsProjectStatusInProjectAction)->execute(999_999, $project->id);

        $this->assertFalse($result);
    }
}
