<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Models\TeamInvitation;
use App\Domains\Teams\Services\ListPendingInvitationsForTeamService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListPendingInvitationsForTeamServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_list_pending_invitations(): void
    {
        [$owner, $team] = $this->makeTeamWithOwner();
        TeamInvitation::factory()->create(['team_id' => $team->id]);
        TeamInvitation::factory()->create(['team_id' => $team->id]);

        $list = app(ListPendingInvitationsForTeamService::class)->execute($owner, $team);

        $this->assertCount(2, $list);
    }

    public function test_non_owner_forbidden(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);

        $this->expectException(AuthorizationException::class);

        app(ListPendingInvitationsForTeamService::class)->execute($member, $team);
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
