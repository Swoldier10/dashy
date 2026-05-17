<?php

namespace Tests\Unit\Domains\Projects\Actions;

use App\Domains\Projects\Actions\FindProjectAction;
use App\Domains\Projects\Models\Project;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindProjectActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_project_when_found(): void
    {
        $project = Project::factory()->create();

        $found = (new FindProjectAction)->execute($project->id);

        $this->assertTrue($project->is($found));
    }

    public function test_throws_when_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        (new FindProjectAction)->execute(99999);
    }
}
