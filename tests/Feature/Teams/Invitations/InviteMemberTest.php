<?php

namespace Tests\Feature\Teams\Invitations;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Mail\TeamInvitationMail;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class InviteMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sends_invitation_via_team_page(): void
    {
        Mail::fake();
        [$owner, $team] = $this->makeTeamWithOwner();
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('inviteEmail', 'fresh@example.com')
            ->set('inviteRole', 'member')
            ->call('invite')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('team_invitations', [
            'team_id' => $team->id,
            'email' => 'fresh@example.com',
            'role' => TeamRole::Member->value,
            'invited_by_user_id' => $owner->id,
        ]);
        Mail::assertSent(TeamInvitationMail::class);
    }

    public function test_self_invite_shows_error(): void
    {
        Mail::fake();
        [$owner, $team] = $this->makeTeamWithOwner();
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('inviteEmail', $owner->email)
            ->set('inviteRole', 'member')
            ->call('invite')
            ->assertHasErrors(['email']);
        Mail::assertNothingSent();
    }

    public function test_existing_member_shows_error(): void
    {
        Mail::fake();
        [$owner, $team] = $this->makeTeamWithOwner();
        $existing = User::factory()->create(['email' => 'already@example.com']);
        $team->members()->attach($existing->id, ['role' => TeamRole::Member->value]);
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('inviteEmail', 'already@example.com')
            ->set('inviteRole', 'member')
            ->call('invite')
            ->assertHasErrors(['email']);
    }

    public function test_duplicate_pending_invitation_shows_error(): void
    {
        Mail::fake();
        [$owner, $team] = $this->makeTeamWithOwner();
        TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'email' => 'pending@example.com',
        ]);
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('inviteEmail', 'pending@example.com')
            ->set('inviteRole', 'member')
            ->call('invite')
            ->assertHasErrors(['email']);
    }

    public function test_owner_role_can_be_invited(): void
    {
        Mail::fake();
        [$owner, $team] = $this->makeTeamWithOwner();
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->set('inviteEmail', 'co-owner@example.com')
            ->set('inviteRole', 'owner')
            ->call('invite')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('team_invitations', [
            'email' => 'co-owner@example.com',
            'role' => TeamRole::Owner->value,
        ]);
    }

    /** @return array{0: User, 1: Team} */
    private function makeTeamWithOwner(): array
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);

        return [$owner, $team];
    }
}
