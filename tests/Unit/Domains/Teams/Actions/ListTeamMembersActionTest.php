<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\ListTeamMembersAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListTeamMembersActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_members_ordered_by_name(): void
    {
        $team = Team::factory()->create();
        $zoe = User::factory()->create(['name' => 'Zoe']);
        $abby = User::factory()->create(['name' => 'Abby']);
        $team->members()->attach($zoe->id, ['role' => TeamRole::Member->value]);
        $team->members()->attach($abby->id, ['role' => TeamRole::Owner->value]);

        $members = (new ListTeamMembersAction)->execute($team);

        $this->assertSame(['Abby', 'Zoe'], $members->pluck('name')->all());
    }

    public function test_returns_empty_collection_for_a_team_with_no_members(): void
    {
        $team = Team::factory()->create();

        $this->assertTrue((new ListTeamMembersAction)->execute($team)->isEmpty());
    }
}
