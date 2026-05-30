<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\FindTeamMemberByIdAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindTeamMemberByIdActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_the_member_with_pivot_role(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);

        $member = (new FindTeamMemberByIdAction)->execute($team, (int) $user->id);

        $this->assertNotNull($member);
        $this->assertTrue($member->is($user));
        $this->assertSame(TeamRole::Member, $member->pivot->role);
    }

    public function test_returns_null_when_not_a_member(): void
    {
        $team = Team::factory()->create();
        $stranger = User::factory()->create();

        $this->assertNull((new FindTeamMemberByIdAction)->execute($team, (int) $stranger->id));
    }
}
