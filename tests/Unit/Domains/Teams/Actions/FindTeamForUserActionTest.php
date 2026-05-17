<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\FindTeamForUserAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindTeamForUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_team_for_member(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create(['name' => 'Acme']);
        $team->members()->attach($user->id, ['role' => TeamRole::Owner->value]);

        $found = (new FindTeamForUserAction)->execute($user, $team->id);

        $this->assertNotNull($found);
        $this->assertSame('Acme', $found->name);
    }

    public function test_returns_null_when_user_is_not_a_member(): void
    {
        $member = User::factory()->create();
        $stranger = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($member->id, ['role' => TeamRole::Owner->value]);

        $found = (new FindTeamForUserAction)->execute($stranger, $team->id);

        $this->assertNull($found);
    }

    public function test_orders_members_owners_first_then_alphabetical(): void
    {
        $owner = User::factory()->create(['name' => 'Zoe Owner']);
        $memberA = User::factory()->create(['name' => 'Adam Member']);
        $memberB = User::factory()->create(['name' => 'Beth Member']);
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($memberA->id, ['role' => TeamRole::Member->value]);
        $team->members()->attach($memberB->id, ['role' => TeamRole::Member->value]);

        $found = (new FindTeamForUserAction)->execute($owner, $team->id);

        $orderedNames = $found->members->pluck('name')->all();
        $this->assertSame(['Zoe Owner', 'Adam Member', 'Beth Member'], $orderedNames);
    }

    public function test_returns_null_for_unknown_team_id(): void
    {
        $user = User::factory()->create();

        $this->assertNull((new FindTeamForUserAction)->execute($user, 999_999));
    }
}
