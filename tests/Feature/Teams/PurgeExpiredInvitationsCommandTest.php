<?php

namespace Tests\Feature\Teams;

use App\Domains\Teams\Models\TeamInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurgeExpiredInvitationsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_purges_only_expired_pending_invitations_and_reports_count(): void
    {
        $expired = TeamInvitation::factory()->expired()->create();
        $active = TeamInvitation::factory()->create();
        $acceptedButExpired = TeamInvitation::factory()->expired()->accepted()->create();
        $revokedButExpired = TeamInvitation::factory()->expired()->revoked()->create();

        $this->artisan('teams:purge-expired-invitations')
            ->expectsOutputToContain('Purged 1 expired invitation(s).')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('team_invitations', ['id' => $expired->id]);
        $this->assertDatabaseHas('team_invitations', ['id' => $active->id]);
        $this->assertDatabaseHas('team_invitations', ['id' => $acceptedButExpired->id]);
        $this->assertDatabaseHas('team_invitations', ['id' => $revokedButExpired->id]);
    }

    public function test_reports_zero_when_nothing_is_expired(): void
    {
        TeamInvitation::factory()->create();

        $this->artisan('teams:purge-expired-invitations')
            ->expectsOutputToContain('Purged 0 expired invitation(s).')
            ->assertExitCode(0);
    }
}
