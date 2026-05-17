<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\ListTeamsForUserAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListTeamsForUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_only_teams_the_user_belongs_to(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $mine = Team::factory()->create(['name' => 'Mine']);
        $theirs = Team::factory()->create(['name' => 'Theirs']);

        $mine->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $theirs->members()->attach($other->id, ['role' => TeamRole::Owner->value]);

        $teams = (new ListTeamsForUserAction)->execute($user);

        $this->assertCount(1, $teams);
        $this->assertSame('Mine', $teams->first()->name);
    }

    public function test_orders_personal_team_first_then_by_created_at(): void
    {
        $user = User::factory()->create();
        $older = Team::factory()->create(['name' => 'Older', 'created_at' => now()->subDays(2)]);
        $newer = Team::factory()->create(['name' => 'Newer', 'created_at' => now()->subDay()]);
        $personal = Team::factory()->personal()->create([
            'name' => 'Personal',
            'created_at' => now(),
        ]);

        foreach ([$older, $newer, $personal] as $t) {
            $t->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        }

        $names = (new ListTeamsForUserAction)->execute($user)->pluck('name')->all();

        $this->assertSame(['Personal', 'Older', 'Newer'], $names);
    }

    public function test_includes_member_count_and_pivot_role(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);

        $teams = (new ListTeamsForUserAction)->execute($owner);

        $this->assertSame(2, $teams->first()->members_count);
        $this->assertSame(TeamRole::Owner, $teams->first()->pivot->role);
    }
}
