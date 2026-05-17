<?php

namespace App\Domains\Projects\Support;

use App\Domains\Projects\Models\Project;

final class ProjectIconShape
{
    public const CIRCLE = 'circle';

    public const TRIANGLE = 'triangle';

    public const PLUS = 'plus';

    /** @var list<self::*> */
    private const SHAPES = [self::CIRCLE, self::TRIANGLE, self::PLUS];

    public static function for(Project $project): string
    {
        $key = (int) $project->id;

        return self::SHAPES[$key % count(self::SHAPES)];
    }
}
