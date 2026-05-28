<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\DeleteExpiredInvitationsAction;
use App\Domains\Teams\Models\TeamInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteExpiredInvitationsActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_only_expired_pending_invitations(): void
    {
        TeamInvitation::factory()->expired()->create();
        TeamInvitation::factory()->expired()->create();
        $pending = TeamInvitation::factory()->create();
        $expiredButAccepted = TeamInvitation::factory()->expired()->accepted()->create();
        $expiredButRevoked = TeamInvitation::factory()->expired()->revoked()->create();

        $deleted = (new DeleteExpiredInvitationsAction)->execute();

        $this->assertSame(2, $deleted);
        $this->assertDatabaseHas('team_invitations', ['id' => $pending->id]);
        $this->assertDatabaseHas('team_invitations', ['id' => $expiredButAccepted->id]);
        $this->assertDatabaseHas('team_invitations', ['id' => $expiredButRevoked->id]);
    }
}
