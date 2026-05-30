<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\ListTeamIdsForUserAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListTeamIdsForUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_ids_of_every_team_the_user_belongs_to(): void
    {
        $user = User::factory()->create();
        $teamA = Team::factory()->create();
        $teamB = Team::factory()->create();
        Team::factory()->create(); // unrelated team
        $teamA->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $teamB->members()->attach($user->id, ['role' => TeamRole::Member->value]);

        $ids = (new ListTeamIdsForUserAction)->execute($user);

        sort($ids);
        $expected = [(int) $teamA->id, (int) $teamB->id];
        sort($expected);
        $this->assertSame($expected, $ids);
        $this->assertContainsOnlyInt($ids);
    }

    public function test_returns_empty_array_when_user_has_no_teams(): void
    {
        $user = User::factory()->create();

        $this->assertSame([], (new ListTeamIdsForUserAction)->execute($user));
    }
}
