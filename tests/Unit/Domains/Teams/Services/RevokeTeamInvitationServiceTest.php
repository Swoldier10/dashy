<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Exceptions\InvalidInvitationException;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Models\TeamInvitation;
use App\Domains\Teams\Services\RevokeTeamInvitationService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevokeTeamInvitationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_revoke_pending_invitation(): void
    {
        [$owner, $team] = $this->makeTeamWithOwner();
        $invitation = TeamInvitation::factory()->create(['team_id' => $team->id]);

        $result = app(RevokeTeamInvitationService::class)->execute($owner, $invitation->id);

        $this->assertNotNull($result->revoked_at);
    }

    public function test_non_owner_forbidden(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);
        $invitation = TeamInvitation::factory()->create(['team_id' => $team->id]);

        $this->expectException(AuthorizationException::class);

        app(RevokeTeamInvitationService::class)->execute($member, $invitation->id);
    }

    public function test_already_revoked_invitation_rejected(): void
    {
        [$owner, $team] = $this->makeTeamWithOwner();
        $invitation = TeamInvitation::factory()->revoked()->create(['team_id' => $team->id]);

        $this->expectException(InvalidInvitationException::class);

        app(RevokeTeamInvitationService::class)->execute($owner, $invitation->id);
    }

    public function test_missing_invitation_throws(): void
    {
        [$owner] = $this->makeTeamWithOwner();

        $this->expectException(InvalidInvitationException::class);

        app(RevokeTeamInvitationService::class)->execute($owner, 999999);
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
