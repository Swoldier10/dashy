<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\UpdateInvitationAcceptedAction;
use App\Domains\Teams\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateInvitationAcceptedActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_marks_invitation_accepted_and_links_user(): void
    {
        $invitation = TeamInvitation::factory()->create();
        $user = User::factory()->create();

        $updated = (new UpdateInvitationAcceptedAction)->execute($invitation, $user);

        $this->assertNotNull($updated->accepted_at);
        $this->assertSame($user->id, $updated->accepted_by_user_id);
        $this->assertDatabaseHas('team_invitations', [
            'id' => $invitation->id,
            'accepted_by_user_id' => $user->id,
        ]);
    }
}
