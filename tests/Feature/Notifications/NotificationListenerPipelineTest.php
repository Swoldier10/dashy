<?php

namespace Tests\Feature\Notifications;

use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Notifications\Mail\NotificationMail;
use App\Domains\Notifications\Models\Notification;
use App\Domains\Notifications\Services\UpdateNotificationPreferencesService;
use App\Domains\Tasks\DTOs\TaskSnapshot;
use App\Domains\Tasks\Events\TaskAssigned;
use App\Domains\Tasks\Events\TaskCreatedInProject;
use App\Domains\Tasks\Events\TaskStatusChanged;
use App\Domains\Teams\Events\TeamInvitationAccepted;
use App\Domains\Teams\Events\TeamMemberJoined;
use App\Domains\Teams\Events\TeamMemberRemoved;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * End-to-end coverage of provider wiring → queued listeners (sync in tests)
 * → NotifyUserService, using manually dispatched snapshot events.
 */
class NotificationListenerPipelineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    private function snapshot(?Team $team = null): TaskSnapshot
    {
        return new TaskSnapshot(
            taskId: 42,
            taskName: 'Fix the build',
            projectId: 7,
            projectName: 'Website',
            teamId: ($team ?? Team::factory()->create())->id,
        );
    }

    public function test_task_assigned_event_creates_a_row_and_queues_the_email(): void
    {
        $actor = User::factory()->create(['first_name' => 'Anna']);
        $assignee = User::factory()->create();
        $team = Team::factory()->create();

        event(new TaskAssigned($this->snapshot($team), $actor->id, $actor->name, $assignee->id));

        $notification = Notification::query()->sole();
        $this->assertSame($assignee->id, $notification->user_id);
        $this->assertSame(NotificationType::TaskAssigned, $notification->type);
        $this->assertSame($team->id, $notification->team_id);
        $this->assertSame('Fix the build', $notification->data['task_name']);
        Mail::assertQueued(NotificationMail::class, 1);
    }

    public function test_self_assignment_produces_nothing(): void
    {
        $actor = User::factory()->create();

        event(new TaskAssigned($this->snapshot(), $actor->id, $actor->name, $actor->id));

        $this->assertSame(0, Notification::query()->count());
        Mail::assertNothingOutgoing();
    }

    public function test_status_change_notifies_assignees_except_the_actor(): void
    {
        $actor = User::factory()->create();
        $assignee = User::factory()->create();

        event(new TaskStatusChanged(
            $this->snapshot(),
            $actor->id,
            $actor->name,
            oldStatusName: 'To do',
            oldCategory: 'not_started',
            newStatusName: 'In progress',
            newCategory: 'active',
            assigneeUserIds: [$actor->id, $assignee->id],
        ));

        $notification = Notification::query()->sole();
        $this->assertSame($assignee->id, $notification->user_id);
        $this->assertSame(NotificationType::TaskStatusChanged, $notification->type);
        $this->assertSame('In progress', $notification->data['new_status_name']);
        Mail::assertNothingOutgoing();
    }

    public function test_moving_into_a_done_category_emits_task_completed(): void
    {
        $actor = User::factory()->create();
        $assignee = User::factory()->create();

        event(new TaskStatusChanged(
            $this->snapshot(),
            $actor->id,
            $actor->name,
            oldStatusName: 'In progress',
            oldCategory: 'active',
            newStatusName: 'Done',
            newCategory: 'done',
            assigneeUserIds: [$assignee->id],
        ));

        $this->assertSame(NotificationType::TaskCompleted, Notification::query()->sole()->type);
    }

    public function test_moving_within_the_done_category_stays_a_status_change(): void
    {
        $actor = User::factory()->create();
        $assignee = User::factory()->create();

        event(new TaskStatusChanged(
            $this->snapshot(),
            $actor->id,
            $actor->name,
            oldStatusName: 'Done',
            oldCategory: 'done',
            newStatusName: 'Shipped',
            newCategory: 'done',
            assigneeUserIds: [$assignee->id],
        ));

        $this->assertSame(NotificationType::TaskStatusChanged, Notification::query()->sole()->type);
    }

    public function test_task_created_skips_assignees_and_respects_the_opt_in_default(): void
    {
        $actor = User::factory()->create();
        $assignee = User::factory()->create();
        $optedIn = User::factory()->create();
        $defaultOff = User::factory()->create();

        app(UpdateNotificationPreferencesService::class)->execute($optedIn, [
            'task_created_in_project' => ['email' => false, 'app' => true],
        ]);

        event(new TaskCreatedInProject(
            $this->snapshot(),
            $actor->id,
            $actor->name,
            teamMemberIds: [$actor->id, $assignee->id, $optedIn->id, $defaultOff->id],
            assigneeUserIds: [$assignee->id],
        ));

        $notification = Notification::query()->sole();
        $this->assertSame($optedIn->id, $notification->user_id);
        $this->assertSame(NotificationType::TaskCreatedInProject, $notification->type);
    }

    public function test_invitation_accepted_notifies_the_inviter_and_joined_notifies_the_rest(): void
    {
        $team = Team::factory()->create(['name' => 'Realba']);
        $inviter = User::factory()->create();
        $joiner = User::factory()->create(['first_name' => 'Mia']);
        $bystander = User::factory()->create();

        event(TeamInvitationAccepted::fromTeam($team, $inviter->id, $joiner));
        event(TeamMemberJoined::fromTeam($team, $joiner, [$inviter->id, $bystander->id], $inviter->id));

        $this->assertSame(
            NotificationType::InvitationAccepted,
            Notification::query()->where('user_id', $inviter->id)->sole()->type,
        );
        $this->assertSame(
            NotificationType::MemberJoined,
            Notification::query()->where('user_id', $bystander->id)->sole()->type,
        );
        // The joiner never notifies themselves; the inviter is not double-notified.
        $this->assertSame(2, Notification::query()->count());
    }

    public function test_member_removal_purges_team_rows_but_keeps_the_removal_notice(): void
    {
        $team = Team::factory()->create();
        $owner = User::factory()->create();
        $removed = User::factory()->create();

        Notification::factory()->count(2)->for($removed, 'recipient')->forTeam($team)->create();

        event(TeamMemberRemoved::fromTeam($team, $removed, $owner));

        $remaining = Notification::query()->where('user_id', $removed->id)->get();
        $this->assertCount(1, $remaining);
        $this->assertSame(NotificationType::RemovedFromTeam, $remaining->first()->type);
    }

    public function test_self_leave_purges_without_creating_a_notice(): void
    {
        $team = Team::factory()->create();
        $leaver = User::factory()->create();

        Notification::factory()->for($leaver, 'recipient')->forTeam($team)->create();

        event(TeamMemberRemoved::fromTeam($team, $leaver, $leaver));

        $this->assertSame(0, Notification::query()->where('user_id', $leaver->id)->count());
    }
}
