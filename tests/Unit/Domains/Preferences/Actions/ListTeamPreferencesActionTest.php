<?php

namespace Tests\Unit\Domains\Preferences\Actions;

use App\Domains\Preferences\Actions\ListTeamPreferencesAction;
use App\Domains\Preferences\Models\TeamPreference;
use App\Domains\Teams\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListTeamPreferencesActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_all_preferences_for_the_team_ordered_by_key(): void
    {
        $team = Team::factory()->create();
        TeamPreference::create(['team_id' => $team->id, 'key' => 'zeta', 'value' => [1]]);
        TeamPreference::create(['team_id' => $team->id, 'key' => 'alpha', 'value' => [2]]);
        TeamPreference::create(['team_id' => Team::factory()->create()->id, 'key' => 'other', 'value' => [3]]);

        $prefs = (new ListTeamPreferencesAction)->execute($team->id);

        $this->assertSame(['alpha', 'zeta'], $prefs->pluck('key')->all());
    }

    public function test_filters_by_key_prefix(): void
    {
        $team = Team::factory()->create();
        TeamPreference::create(['team_id' => $team->id, 'key' => 'memory.a', 'value' => [1]]);
        TeamPreference::create(['team_id' => $team->id, 'key' => 'tz', 'value' => [2]]);

        $prefs = (new ListTeamPreferencesAction)->execute($team->id, 'memory.');

        $this->assertSame(['memory.a'], $prefs->pluck('key')->all());
    }
}
