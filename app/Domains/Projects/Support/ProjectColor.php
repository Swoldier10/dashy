<?php

namespace App\Domains\Projects\Support;

use App\Domains\Projects\Models\Project;

final class ProjectColor
{
    /**
     * Stable color CSS variable per project. Derived from id so that the
     * sidebar dot and the row pill always match for the same project. When
     * a real `color` column lands, this helper is the only place to update.
     *
     * @var list<string>
     */
    private const PALETTE = [
        '--blue',
        '--state-success',
        '--state-warning',
        '--accent',
        '--blue-deep',
        '--cocoa',
    ];

    public static function for(Project $project): string
    {
        $key = (int) $project->id;

        return self::PALETTE[$key % count(self::PALETTE)];
    }
}
