<?php

namespace Tests\Unit\Domains\Projects\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Projects\Services\AssertProjectStatusInProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AssertProjectStatusInProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_passes_when_status_belongs_to_project(): void
    {
        $project = Project::factory()->create();
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);

        app(AssertProjectStatusInProjectService::class)->execute($status->id, $project->id);

        $this->expectNotToPerformAssertions();
    }

    public function test_throws_with_exact_validation_key_and_message(): void
    {
        $project = Project::factory()->create();
        $other = Project::factory()->create();
        $foreign = ProjectStatus::factory()->create(['project_id' => $other->id]);

        try {
            app(AssertProjectStatusInProjectService::class)->execute($foreign->id, $project->id);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('project_status_id', $e->errors());
            $this->assertSame(
                __('The selected status does not belong to this project.'),
                $e->errors()['project_status_id'][0],
            );
        }
    }
}
