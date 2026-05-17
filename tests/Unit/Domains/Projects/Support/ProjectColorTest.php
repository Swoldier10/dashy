<?php

namespace Tests\Unit\Domains\Projects\Support;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Support\ProjectColor;
use Tests\TestCase;

class ProjectColorTest extends TestCase
{
    public function test_returns_a_palette_token(): void
    {
        $project = new Project;
        $project->id = 1;

        $this->assertStringStartsWith('--', ProjectColor::for($project));
    }

    public function test_is_stable_for_the_same_id(): void
    {
        $a = new Project;
        $a->id = 42;
        $b = new Project;
        $b->id = 42;

        $this->assertSame(ProjectColor::for($a), ProjectColor::for($b));
    }

    public function test_distributes_across_palette(): void
    {
        $tokens = [];
        for ($i = 0; $i < 12; $i++) {
            $project = new Project;
            $project->id = $i;
            $tokens[] = ProjectColor::for($project);
        }

        $this->assertGreaterThanOrEqual(6, count(array_unique($tokens)));
    }
}
