<?php

namespace Tests\Feature\Teams;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Events\TeamInvitationAccepted;
use App\Domains\Teams\Events\TeamMemberJoined;
use App\Domains\Teams\Events\TeamMemberRemoved;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Models\TeamInvitation;
use App\Domains\Teams\Services\AcceptTeamInvitationService;
use App\Domains\Teams\Services\AddTeamMemberService;
use App\Domains\Teams\Services\RemoveTeamMemberService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TeamNotificationEventsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([
            TeamInvitationAccepted::class,
            TeamMemberJoined::class,
            TeamMemberRemoved::class,
        ]);
    }

    /**
     * @return array{0: User, 1: Team}
     */
    private function teamWithOwner(): array
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);

        return [$owner, $team];
    }

    private function invitationFor(Team $team, User $owner, string $email, string $plainToken): TeamInvitation
    {
        return TeamInvitation::create([
            'team_id' => $team->id,
            'email' => $email,
            'role' => TeamRole::Member->value,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->addDays(7),
            'invited_by_user_id' => $owner->id,
            'last_sent_at' => now(),
        ]);
    }

    public function test_accepting_an_invitation_dispatches_accepted_and_joined(): void
    {
        [$owner, $team] = $this->teamWithOwner();
        $joiner = User::factory()->create();
        $this->invitationFor($team, $owner, $joiner->email, 'plain-token');

        app(AcceptTeamInvitationService::class)->execute($joiner, 'plain-token');

        Event::assertDispatched(TeamInvitationAccepted::class, function (TeamInvitationAccepted $event) use ($owner, $joiner, $team) {
            return $event->invitedByUserId === $owner->id
                && $event->acceptedByUserId === $joiner->id
                && $event->teamId === $team->id;
        });
        Event::assertDispatched(TeamMemberJoined::class, function (TeamMemberJoined $event) use ($owner, $joiner) {
            return $event->joinedUserId === $joiner->id
                && $event->otherMemberIds === [$owner->id]
                && $event->invitedByUserId === $owner->id;
        });
    }

    public function test_re_accepting_an_already_accepted_invitation_dispatches_neither_event(): void
    {
        [$owner, $team] = $this->teamWithOwner();
        $joiner = User::factory()->create();
        $this->invitationFor($team, $owner, $joiner->email, 'plain-token');

        app(AcceptTeamInvitationService::class)->execute($joiner, 'plain-token');
        app(AcceptTeamInvitationService::class)->execute($joiner, 'plain-token');

        Event::assertDispatchedTimes(TeamInvitationAccepted::class, 1);
        Event::assertDispatchedTimes(TeamMemberJoined::class, 1);
    }

    public function test_directly_adding_a_member_dispatches_joined(): void
    {
        [$owner, $team] = $this->teamWithOwner();
        $target = User::factory()->create();

        app(AddTeamMemberService::class)->execute($owner, $team, ['email' => $target->email]);

        Event::assertDispatched(TeamMemberJoined::class, function (TeamMemberJoined $event) use ($owner, $target) {
            return $event->joinedUserId === $target->id
                && $event->otherMemberIds === [$owner->id]
                && $event->invitedByUserId === null;
        });
    }

    public function test_removing_a_member_dispatches_removed_with_self_leave_false(): void
    {
        [$owner, $team] = $this->teamWithOwner();
        $target = User::factory()->create();
        $team->members()->attach($target->id, ['role' => TeamRole::Member->value]);

        app(RemoveTeamMemberService::class)->execute($owner, $team, $target);

        Event::assertDispatched(TeamMemberRemoved::class, function (TeamMemberRemoved $event) use ($owner, $target) {
            return $event->removedUserId === $target->id
                && $event->actorUserId === $owner->id
                && $event->wasSelfLeave === false;
        });
    }

    public function test_self_leave_dispatches_removed_with_self_leave_true(): void
    {
        [$owner, $team] = $this->teamWithOwner();
        $leaver = User::factory()->create();
        $team->members()->attach($leaver->id, ['role' => TeamRole::Member->value]);

        app(RemoveTeamMemberService::class)->execute($leaver, $team, $leaver);

        Event::assertDispatched(TeamMemberRemoved::class, fn (TeamMemberRemoved $event) => $event->wasSelfLeave === true);
    }
}
