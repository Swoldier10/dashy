<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\ListSoloTeamIdsForUserAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListSoloTeamIdsForUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_only_teams_where_the_user_is_the_sole_member(): void
    {
        $user = User::factory()->create();
        $solo = Team::factory()->create();
        $shared = Team::factory()->create();
        $solo->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $shared->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $shared->members()->attach(User::factory()->create()->id, ['role' => TeamRole::Member->value]);

        $ids = (new ListSoloTeamIdsForUserAction)->execute($user);

        $this->assertSame([(int) $solo->id], $ids);
    }

    public function test_returns_empty_array_when_user_has_no_solo_teams(): void
    {
        $user = User::factory()->create();

        $this->assertSame([], (new ListSoloTeamIdsForUserAction)->execute($user));
    }
}
