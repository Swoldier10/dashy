<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\FindTeamAction;
use App\Domains\Teams\Models\Team;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindTeamActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_team_when_found(): void
    {
        $team = Team::factory()->create(['name' => 'Acme']);

        $found = (new FindTeamAction)->execute($team->id);

        $this->assertTrue($team->is($found));
        $this->assertSame('Acme', $found->name);
    }

    public function test_throws_when_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        (new FindTeamAction)->execute(99999);
    }
}
