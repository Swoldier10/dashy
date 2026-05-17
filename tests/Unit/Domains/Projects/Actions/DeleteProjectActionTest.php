<?php

namespace Tests\Unit\Domains\Projects\Actions;

use App\Domains\Projects\Actions\DeleteProjectAction;
use App\Domains\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteProjectActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_project(): void
    {
        $project = Project::factory()->create();

        (new DeleteProjectAction)->execute($project);

        $this->assertSame(0, Project::count());
    }
}
