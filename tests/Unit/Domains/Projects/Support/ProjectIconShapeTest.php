<?php

namespace Tests\Unit\Domains\Projects\Support;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Support\ProjectIconShape;
use Tests\TestCase;

class ProjectIconShapeTest extends TestCase
{
    public function test_returns_a_known_shape(): void
    {
        $project = new Project;
        $project->id = 7;

        $this->assertContains(ProjectIconShape::for($project), [
            ProjectIconShape::CIRCLE,
            ProjectIconShape::TRIANGLE,
            ProjectIconShape::PLUS,
        ]);
    }

    public function test_is_stable_for_the_same_id(): void
    {
        $a = new Project;
        $a->id = 99;
        $b = new Project;
        $b->id = 99;

        $this->assertSame(ProjectIconShape::for($a), ProjectIconShape::for($b));
    }

    public function test_uses_all_three_shapes(): void
    {
        $shapes = [];
        for ($i = 0; $i < 6; $i++) {
            $project = new Project;
            $project->id = $i;
            $shapes[] = ProjectIconShape::for($project);
        }

        $this->assertCount(3, array_unique($shapes));
    }
}
