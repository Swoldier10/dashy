<?php

namespace Tests\Feature\Teams;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DeleteTeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_delete_non_personal_team(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->call('deleteTeam')
            ->assertHasNoErrors()
            ->assertRedirect(route('teams.index'));

        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    }

    public function test_personal_team_cannot_be_deleted(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $team = $user->teams()->where('teams.personal_team', true)->first();
        $this->actingAs($user);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->call('deleteTeam')
            ->assertHasErrors('team');

        $this->assertDatabaseHas('teams', ['id' => $team->id]);
    }

    public function test_member_cannot_delete(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);
        $this->actingAs($member);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->call('deleteTeam')
            ->assertForbidden();

        $this->assertDatabaseHas('teams', ['id' => $team->id]);
    }
}
