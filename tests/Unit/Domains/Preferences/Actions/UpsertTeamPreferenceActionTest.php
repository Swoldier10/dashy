<?php

namespace Tests\Unit\Domains\Preferences\Actions;

use App\Domains\Preferences\Actions\UpsertTeamPreferenceAction;
use App\Domains\Preferences\Models\TeamPreference;
use App\Domains\Teams\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpsertTeamPreferenceActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_a_preference_when_none_exists(): void
    {
        $team = Team::factory()->create();

        $pref = (new UpsertTeamPreferenceAction)->execute($team->id, 'tz', ['zone' => 'UTC']);

        $this->assertSame(['zone' => 'UTC'], $pref->value);
        $this->assertDatabaseHas('team_preferences', ['team_id' => $team->id, 'key' => 'tz']);
    }

    public function test_updates_the_existing_row_for_the_same_key(): void
    {
        $team = Team::factory()->create();
        (new UpsertTeamPreferenceAction)->execute($team->id, 'tz', ['zone' => 'UTC']);

        (new UpsertTeamPreferenceAction)->execute($team->id, 'tz', ['zone' => 'CET']);

        $this->assertSame(1, TeamPreference::query()->where('team_id', $team->id)->where('key', 'tz')->count());
        $this->assertSame(['zone' => 'CET'], TeamPreference::query()->where('team_id', $team->id)->where('key', 'tz')->first()->value);
    }
}
