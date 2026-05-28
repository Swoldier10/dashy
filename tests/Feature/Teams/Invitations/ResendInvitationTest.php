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

class ResendInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_resend_after_throttle_window(): void
    {
        Mail::fake();
        [$owner, $team] = $this->makeTeamWithOwner();
        $invitation = TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'token_hash' => str_repeat('a', 64),
            'last_sent_at' => now()->subHours(2),
        ]);
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->call('resend', $invitation->id);

        $invitation->refresh();
        $this->assertNotSame(str_repeat('a', 64), $invitation->token_hash);
        Mail::assertSent(TeamInvitationMail::class);
    }

    public function test_resend_within_throttle_window_is_blocked(): void
    {
        Mail::fake();
        [$owner, $team] = $this->makeTeamWithOwner();
        $invitation = TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'token_hash' => str_repeat('b', 64),
            'last_sent_at' => now()->subMinutes(10),
        ]);
        $this->actingAs($owner);

        Livewire::test('pages::teams.show', ['team' => $team->id])
            ->call('resend', $invitation->id);

        $invitation->refresh();
        $this->assertSame(str_repeat('b', 64), $invitation->token_hash);
        Mail::assertNothingSent();
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
