<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\CreateTeamAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTeamActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_team_with_attributes(): void
    {
        $team = (new CreateTeamAction)->execute([
            'name' => 'Acme',
            'personal_team' => false,
        ]);

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'Acme',
            'personal_team' => 0,
        ]);
    }

    public function test_personal_team_flag_defaults_to_false(): void
    {
        $team = (new CreateTeamAction)->execute(['name' => 'Plain']);

        $this->assertFalse($team->personal_team);
    }

    public function test_creates_personal_team_when_flag_set(): void
    {
        $team = (new CreateTeamAction)->execute([
            'name' => 'Pat\'s Team',
            'personal_team' => true,
        ]);

        $this->assertTrue($team->personal_team);
    }
}
