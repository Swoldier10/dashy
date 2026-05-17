<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\UpdateTeamAction;
use App\Domains\Teams\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTeamActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_team_attributes(): void
    {
        $team = Team::factory()->create(['name' => 'Old']);

        $updated = (new UpdateTeamAction)->execute($team, ['name' => 'New']);

        $this->assertSame('New', $updated->name);
        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'New',
        ]);
    }
}
