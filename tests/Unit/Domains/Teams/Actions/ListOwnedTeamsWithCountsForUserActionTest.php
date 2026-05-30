<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\ListOwnedTeamsWithCountsForUserAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListOwnedTeamsWithCountsForUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_owned_teams_with_member_and_owner_counts(): void
    {
        $user = User::factory()->create();
        $owned = Team::factory()->create();
        $owned->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $owned->members()->attach(User::factory()->create()->id, ['role' => TeamRole::Member->value]);
        $owned->members()->attach(User::factory()->create()->id, ['role' => TeamRole::Owner->value]);

        $result = (new ListOwnedTeamsWithCountsForUserAction)->execute($user);

        $this->assertCount(1, $result);
        $this->assertSame(3, (int) $result->first()->members_count);
        $this->assertSame(2, (int) $result->first()->owners_count);
    }

    public function test_excludes_teams_where_the_user_is_only_a_member(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);

        $this->assertTrue((new ListOwnedTeamsWithCountsForUserAction)->execute($user)->isEmpty());
    }
}
