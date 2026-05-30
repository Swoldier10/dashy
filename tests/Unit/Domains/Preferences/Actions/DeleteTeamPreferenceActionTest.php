<?php

namespace Tests\Unit\Domains\Preferences\Actions;

use App\Domains\Preferences\Actions\DeleteTeamPreferenceAction;
use App\Domains\Preferences\Models\TeamPreference;
use App\Domains\Teams\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTeamPreferenceActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_the_matching_key_and_returns_the_count(): void
    {
        $team = Team::factory()->create();
        TeamPreference::create(['team_id' => $team->id, 'key' => 'memory.t', 'value' => ['f' => 1]]);

        $deleted = (new DeleteTeamPreferenceAction)->execute($team->id, 'memory.t');

        $this->assertSame(1, $deleted);
        $this->assertDatabaseMissing('team_preferences', ['team_id' => $team->id, 'key' => 'memory.t']);
    }

    public function test_returns_zero_when_no_row_matches(): void
    {
        $team = Team::factory()->create();

        $this->assertSame(0, (new DeleteTeamPreferenceAction)->execute($team->id, 'memory.absent'));
    }
}
