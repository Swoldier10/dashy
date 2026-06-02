<?php

namespace Tests\Feature\Notifications;

use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Models\Event;
use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Notifications\Mail\NotificationMail;
use App\Domains\Notifications\Models\Notification;
use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DispatchRemindersCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->travelTo('2026-06-02 12:00:00');
    }

    private function taskWithAssignees(string $endDate, ProjectStatusCategory $category, array $assignees): Task
    {
        $team = Team::factory()->create();
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => $category->value,
        ]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
            'created_by_user_id' => User::factory()->create()->id,
            'end_date' => $endDate,
        ]);

        foreach ($assignees as $assignee) {
            $task->assignees()->attach($assignee->id, ['assigned_by_user_id' => $assignee->id]);
        }

        return $task;
    }

    public function test_due_soon_reminds_each_assignee_once_and_queues_emails(): void
    {
        $anna = User::factory()->create();
        $ben = User::factory()->create();
        $this->taskWithAssignees('2026-06-02 18:00:00', ProjectStatusCategory::Active, [$anna, $ben]);

        $this->artisan('notifications:dispatch-reminders')->assertSuccessful();
        $this->artisan('notifications:dispatch-reminders')->assertSuccessful();

        $this->assertSame(2, Notification::query()->where('type', NotificationType::TaskDueSoon->value)->count());
        Mail::assertQueued(NotificationMail::class, 2);
    }

    public function test_tasks_outside_the_window_or_completed_produce_nothing(): void
    {
        $anna = User::factory()->create();
        $this->taskWithAssignees('2026-06-05 18:00:00', ProjectStatusCategory::Active, [$anna]);
        $this->taskWithAssignees('2026-06-02 18:00:00', ProjectStatusCategory::Done, [$anna]);

        $this->artisan('notifications:dispatch-reminders')->assertSuccessful();

        $this->assertSame(0, Notification::query()->count());
    }

    public function test_overdue_tasks_remind_assignees_idempotently(): void
    {
        $anna = User::factory()->create();
        $this->taskWithAssignees('2026-06-01 09:00:00', ProjectStatusCategory::Active, [$anna]);

        $this->artisan('notifications:dispatch-reminders')->assertSuccessful();
        $this->artisan('notifications:dispatch-reminders')->assertSuccessful();

        $notification = Notification::query()->where('type', NotificationType::TaskOverdue->value)->sole();
        $this->assertSame($anna->id, $notification->user_id);
    }

    public function test_changing_the_due_date_re_arms_the_reminder(): void
    {
        $anna = User::factory()->create();
        $task = $this->taskWithAssignees('2026-06-02 18:00:00', ProjectStatusCategory::Active, [$anna]);

        $this->artisan('notifications:dispatch-reminders')->assertSuccessful();
        $task->update(['end_date' => '2026-06-02 20:00:00']);
        $this->artisan('notifications:dispatch-reminders')->assertSuccessful();

        $this->assertSame(2, Notification::query()->where('type', NotificationType::TaskDueSoon->value)->count());
    }

    public function test_event_starting_soon_reminds_the_owner_once(): void
    {
        $owner = User::factory()->create();
        Event::factory()->forUser($owner)->create([
            'start_at' => '2026-06-02 12:10:00',
            'end_at' => '2026-06-02 13:00:00',
        ]);
        Event::factory()->forUser($owner)->create([
            'start_at' => '2026-06-02 15:00:00',
            'end_at' => '2026-06-02 16:00:00',
        ]);

        $this->artisan('notifications:dispatch-reminders')->assertSuccessful();
        $this->artisan('notifications:dispatch-reminders')->assertSuccessful();

        $notification = Notification::query()->where('type', NotificationType::EventStartingSoon->value)->sole();
        $this->assertSame($owner->id, $notification->user_id);
        $this->assertSame('2026-06-02T12:10:00+00:00', $notification->data['starts_at']);
    }

    public function test_recurring_event_occurrences_have_stable_dedupe_across_runs(): void
    {
        $owner = User::factory()->create();
        Event::factory()->forUser($owner)->recurring(RecurrenceFreq::Daily)->create([
            'start_at' => '2026-05-01 12:15:00',
            'end_at' => '2026-05-01 12:45:00',
        ]);

        $this->artisan('notifications:dispatch-reminders')->assertSuccessful();
        $this->artisan('notifications:dispatch-reminders')->assertSuccessful();

        // Today's 12:15 occurrence — exactly one row despite two runs.
        $this->assertSame(
            1,
            Notification::query()->where('type', NotificationType::EventStartingSoon->value)->count(),
        );
    }
}
