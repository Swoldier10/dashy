<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\CreateInvitationAction;
use App\Domains\Teams\DTOs\CreateInvitationData;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateInvitationActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_inserts_a_pending_invitation_row(): void
    {
        $team = Team::factory()->create();
        $inviter = User::factory()->create();
        $now = CarbonImmutable::now();

        $invitation = (new CreateInvitationAction)->execute(new CreateInvitationData(
            teamId: $team->id,
            email: 'newperson@example.com',
            role: TeamRole::Member,
            tokenHash: str_repeat('a', 64),
            expiresAt: $now->addDays(7),
            invitedByUserId: $inviter->id,
            lastSentAt: $now,
        ));

        $this->assertNotNull($invitation->id);
        $this->assertSame('newperson@example.com', $invitation->email);
        $this->assertSame(TeamRole::Member, $invitation->role);
        $this->assertSame(str_repeat('a', 64), $invitation->token_hash);
        $this->assertNull($invitation->accepted_at);
        $this->assertNull($invitation->revoked_at);
        $this->assertDatabaseHas('team_invitations', [
            'id' => $invitation->id,
            'team_id' => $team->id,
            'email' => 'newperson@example.com',
            'invited_by_user_id' => $inviter->id,
        ]);
    }
}
