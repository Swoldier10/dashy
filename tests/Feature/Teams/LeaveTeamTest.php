<?php

namespace Tests\Feature\Teams;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeaveTeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_leave_team_and_is_redirected(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);
        $this->actingAs($member);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->call('confirmRemoveMember', $member->id)
            ->call('removeMember')
            ->assertHasNoErrors()
            ->assertRedirect(route('teams.index'));

        $this->assertDatabaseMissing('team_user', [
            'team_id' => $team->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_user_cannot_leave_personal_team(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $team = $user->teams()->where('teams.personal_team', true)->first();
        $this->actingAs($user);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->call('confirmRemoveMember', $user->id)
            ->call('removeMember')
            ->assertHasErrors('team');

        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $user->id,
        ]);
    }
}
