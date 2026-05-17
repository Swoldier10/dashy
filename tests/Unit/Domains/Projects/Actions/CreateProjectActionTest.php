<?php

namespace Tests\Unit\Domains\Projects\Actions;

use App\Domains\Projects\Actions\CreateProjectAction;
use App\Domains\Teams\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateProjectActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_project_with_provided_attributes(): void
    {
        $team = Team::factory()->create();

        $project = (new CreateProjectAction)->execute([
            'team_id' => $team->id,
            'name' => 'Roof installation',
            'description' => 'For client X',
            'logo' => 'https://example.test/logo.png',
        ]);

        $this->assertSame('Roof installation', $project->name);
        $this->assertSame('For client X', $project->description);
        $this->assertSame('https://example.test/logo.png', $project->logo);
        $this->assertSame($team->id, $project->team_id);
        $this->assertNotNull($project->id);
    }

    public function test_defaults_description_and_logo_to_null_when_omitted(): void
    {
        $team = Team::factory()->create();

        $project = (new CreateProjectAction)->execute([
            'team_id' => $team->id,
            'name' => 'Bare project',
        ]);

        $this->assertNull($project->description);
        $this->assertNull($project->logo);
    }
}
