<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\UpdateInvitationRevokedAction;
use App\Domains\Teams\Models\TeamInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateInvitationRevokedActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_marks_invitation_revoked(): void
    {
        $invitation = TeamInvitation::factory()->create();

        $updated = (new UpdateInvitationRevokedAction)->execute($invitation);

        $this->assertNotNull($updated->revoked_at);
        $this->assertDatabaseHas('team_invitations', [
            'id' => $invitation->id,
        ]);
        $this->assertNotNull($updated->fresh()->revoked_at);
    }
}
