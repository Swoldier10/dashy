<?php

namespace Tests\Feature\Teams;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected(): void
    {
        $team = Team::factory()->create();

        $this->get(route('teams.show', $team))->assertRedirect(route('login'));
    }

    public function test_member_can_view_team(): void
    {
        $user = User::factory()->create(['name' => 'Alex Member']);
        $team = Team::factory()->create(['name' => 'Acme']);
        $team->members()->attach($user->id, ['role' => TeamRole::Owner->value]);

        $response = $this->actingAs($user)->get(route('teams.show', $team));

        $response->assertOk();
        $response->assertSeeText('Acme');
        $response->assertSeeText('Alex Member');
    }

    public function test_non_member_gets_404(): void
    {
        $member = User::factory()->create();
        $stranger = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($member->id, ['role' => TeamRole::Owner->value]);

        $this->actingAs($stranger)
            ->get(route('teams.show', $team))
            ->assertNotFound();
    }

    public function test_unknown_team_id_returns_404(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $this->actingAs($user)
            ->get(route('teams.show', ['team' => 999_999]))
            ->assertNotFound();
    }

    public function test_invite_form_is_hidden_on_a_personal_team(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->personal()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);

        $this->actingAs($owner)
            ->get(route('teams.show', $team))
            ->assertOk()
            ->assertDontSeeText('Invite member by email');
    }

    public function test_invite_form_is_shown_to_owner_on_a_shared_team(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create(); // shared (personal_team = false)
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);

        $this->actingAs($owner)
            ->get(route('teams.show', $team))
            ->assertOk()
            ->assertSeeText('Invite member by email');
    }
}
