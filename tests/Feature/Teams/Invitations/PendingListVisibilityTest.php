<?php

namespace Tests\Feature\Teams\Invitations;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PendingListVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sees_pending_invitations_on_team_page(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'email' => 'pending1@example.com',
        ]);
        TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'email' => 'pending2@example.com',
        ]);
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->assertSeeText('Pending invitations')
            ->assertSeeText('pending1@example.com')
            ->assertSeeText('pending2@example.com');
    }

    public function test_pending_invitations_collection_is_empty_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);
        TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'email' => 'hidden@example.com',
        ]);
        $this->actingAs($member);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->assertDontSeeText('Pending invitations')
            ->assertDontSeeText('hidden@example.com');
    }
}
