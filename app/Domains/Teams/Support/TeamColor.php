<?php

namespace App\Domains\Teams\Support;

use App\Domains\Teams\Models\Team;

final class TeamColor
{
    /** @var list<string> */
    private const PALETTE = [
        '--state-error',
        '--state-success',
        '--state-warning',
        '--blue',
    ];

    public static function for(Team $team): string
    {
        $key = (int) $team->id;

        return self::PALETTE[$key % count(self::PALETTE)];
    }
}
