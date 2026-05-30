<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\ListTeamMemberIdsAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListTeamMemberIdsActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_member_ids_as_integers(): void
    {
        $team = Team::factory()->create();
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);

        $ids = (new ListTeamMemberIdsAction)->execute($team);

        sort($ids);
        $expected = [$owner->id, $member->id];
        sort($expected);
        $this->assertSame($expected, $ids);
        $this->assertContainsOnly('integer', $ids);
    }

    public function test_returns_empty_for_team_without_members(): void
    {
        $team = Team::factory()->create();

        $this->assertSame([], (new ListTeamMemberIdsAction)->execute($team));
    }
}
