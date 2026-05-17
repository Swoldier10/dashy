<?php

namespace Tests\Feature\Teams;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AddMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_add_existing_user(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create(['email' => 'invitee@example.com']);
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('inviteEmail', 'invitee@example.com')
            ->call('addMember')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $invitee->id,
            'role' => TeamRole::Member->value,
        ]);
    }

    public function test_unknown_email_shows_error(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('inviteEmail', 'nope@example.com')
            ->call('addMember')
            ->assertHasErrors('email');
    }

    public function test_duplicate_member_shows_error(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create(['email' => 'member@example.com']);
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('inviteEmail', 'member@example.com')
            ->call('addMember')
            ->assertHasErrors('email');
    }
}
