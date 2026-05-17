<?php

namespace Tests\Unit\Domains\Teams\Support;

use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Support\TeamColor;
use Tests\TestCase;

class TeamColorTest extends TestCase
{
    public function test_returns_a_palette_token(): void
    {
        $team = new Team;
        $team->id = 3;

        $this->assertStringStartsWith('--', TeamColor::for($team));
    }

    public function test_is_stable_for_the_same_id(): void
    {
        $a = new Team;
        $a->id = 12;
        $b = new Team;
        $b->id = 12;

        $this->assertSame(TeamColor::for($a), TeamColor::for($b));
    }

    public function test_distributes_across_palette(): void
    {
        $tokens = [];
        for ($i = 0; $i < 8; $i++) {
            $team = new Team;
            $team->id = $i;
            $tokens[] = TeamColor::for($team);
        }

        $this->assertGreaterThanOrEqual(4, count(array_unique($tokens)));
    }
}
