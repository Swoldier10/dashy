<?php

namespace Tests\Feature\Tasks;

use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Events\TaskAssigned;
use App\Domains\Tasks\Events\TaskAttachmentAdded;
use App\Domains\Tasks\Events\TaskCreatedInProject;
use App\Domains\Tasks\Events\TaskDueDateChanged;
use App\Domains\Tasks\Events\TaskPriorityChanged;
use App\Domains\Tasks\Events\TaskStatusChanged;
use App\Domains\Tasks\Events\TaskUnassigned;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\AddTaskAttachmentsService;
use App\Domains\Tasks\Services\AssignTaskService;
use App\Domains\Tasks\Services\BulkAssignTasksService;
use App\Domains\Tasks\Services\BulkMoveTasksService;
use App\Domains\Tasks\Services\BulkUpdateTaskDueDateService;
use App\Domains\Tasks\Services\BulkUpdateTaskPriorityService;
use App\Domains\Tasks\Services\CreateTaskService;
use App\Domains\Tasks\Services\MoveTaskService;
use App\Domains\Tasks\Services\ToggleTaskCompleteService;
use App\Domains\Tasks\Services\UnassignTaskService;
use App\Domains\Tasks\Services\UpdateTaskDatesService;
use App\Domains\Tasks\Services\UpdateTaskPriorityService;
use App\Domains\Tasks\Services\UpdateTaskStatusService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Asserts every Tasks mutation service dispatches its notification event
 * with the right snapshot — and stays silent on no-op paths.
 */
class TaskNotificationEventsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $member;

    private Team $team;

    private Project $project;

    private ProjectStatus $todo;

    private ProjectStatus $doing;

    private ProjectStatus $done;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([
            TaskAssigned::class,
            TaskUnassigned::class,
            TaskStatusChanged::class,
            TaskDueDateChanged::class,
            TaskPriorityChanged::class,
            TaskAttachmentAdded::class,
            TaskCreatedInProject::class,
        ]);

        $this->owner = User::factory()->create();
        $this->member = User::factory()->create();
        $this->team = Team::factory()->create();
        $this->team->members()->attach($this->owner->id, ['role' => TeamRole::Owner->value]);
        $this->team->members()->attach($this->member->id, ['role' => TeamRole::Member->value]);
        $this->project = Project::factory()->create(['team_id' => $this->team->id]);
        $this->todo = ProjectStatus::factory()->create([
            'project_id' => $this->project->id, 'name' => 'To do', 'position' => 0,
            'category' => ProjectStatusCategory::NotStarted->value,
        ]);
        $this->doing = ProjectStatus::factory()->create([
            'project_id' => $this->project->id, 'name' => 'Doing', 'position' => 1,
            'category' => ProjectStatusCategory::Active->value,
        ]);
        $this->done = ProjectStatus::factory()->create([
            'project_id' => $this->project->id, 'name' => 'Done', 'position' => 2,
            'category' => ProjectStatusCategory::Done->value,
        ]);
    }

    private function makeTask(array $overrides = []): Task
    {
        return Task::factory()->create(array_merge([
            'project_id' => $this->project->id,
            'project_status_id' => $this->todo->id,
            'created_by_user_id' => $this->owner->id,
        ], $overrides));
    }

    public function test_assigning_dispatches_task_assigned_with_the_snapshot(): void
    {
        $task = $this->makeTask();

        app(AssignTaskService::class)->execute($this->owner, $task->id, $this->member->id);

        Event::assertDispatched(TaskAssigned::class, function (TaskAssigned $event) use ($task) {
            return $event->assigneeUserId === $this->member->id
                && $event->actorUserId === $this->owner->id
                && $event->task->taskId === $task->id
                && $event->task->teamId === $this->team->id
                && $event->task->taskName === $task->name;
        });
    }

    public function test_reassigning_an_already_assigned_user_dispatches_nothing(): void
    {
        $task = $this->makeTask();
        app(AssignTaskService::class)->execute($this->owner, $task->id, $this->member->id);

        app(AssignTaskService::class)->execute($this->owner, $task->id, $this->member->id);

        Event::assertDispatchedTimes(TaskAssigned::class, 1);
    }

    public function test_bulk_assign_dispatches_only_for_newly_assigned_tasks(): void
    {
        $taskA = $this->makeTask();
        $taskB = $this->makeTask();
        app(AssignTaskService::class)->execute($this->owner, $taskA->id, $this->member->id);

        app(BulkAssignTasksService::class)->execute($this->owner, [$taskA->id, $taskB->id], $this->member->id);

        // One from the initial single assign + one for taskB in the bulk run.
        Event::assertDispatchedTimes(TaskAssigned::class, 2);
    }

    public function test_unassigning_dispatches_task_unassigned_only_when_the_user_was_assigned(): void
    {
        $task = $this->makeTask();
        app(AssignTaskService::class)->execute($this->owner, $task->id, $this->member->id);

        app(UnassignTaskService::class)->execute($this->owner, $task->id, $this->member->id);
        app(UnassignTaskService::class)->execute($this->owner, $task->id, $this->member->id);

        Event::assertDispatchedTimes(TaskUnassigned::class, 1);
        Event::assertDispatched(TaskUnassigned::class, fn (TaskUnassigned $event) => $event->removedUserId === $this->member->id);
    }

    public function test_status_change_dispatches_with_old_and_new_snapshot(): void
    {
        $task = $this->makeTask();
        app(AssignTaskService::class)->execute($this->owner, $task->id, $this->member->id);

        app(UpdateTaskStatusService::class)->execute($this->owner, $task->id, $this->doing->id);

        Event::assertDispatched(TaskStatusChanged::class, function (TaskStatusChanged $event) {
            return $event->oldStatusName === 'To do'
                && $event->newStatusName === 'Doing'
                && $event->oldCategory === ProjectStatusCategory::NotStarted->value
                && $event->newCategory === ProjectStatusCategory::Active->value
                && $event->assigneeUserIds === [$this->member->id]
                && ! $event->becameDone();
        });
    }

    public function test_unchanged_status_dispatches_nothing(): void
    {
        $task = $this->makeTask();

        app(UpdateTaskStatusService::class)->execute($this->owner, $task->id, $this->todo->id);

        Event::assertNotDispatched(TaskStatusChanged::class);
    }

    public function test_moving_into_done_reports_became_done(): void
    {
        $task = $this->makeTask();

        app(UpdateTaskStatusService::class)->execute($this->owner, $task->id, $this->done->id);

        Event::assertDispatched(TaskStatusChanged::class, fn (TaskStatusChanged $event) => $event->becameDone());
    }

    public function test_drag_move_dispatches_only_when_the_status_actually_changes(): void
    {
        $task = $this->makeTask();

        app(MoveTaskService::class)->execute($this->owner, $task->id, $this->doing->id, [], [$task->id]);
        app(MoveTaskService::class)->execute($this->owner, $task->id, $this->doing->id, [], [$task->id]);

        Event::assertDispatchedTimes(TaskStatusChanged::class, 1);
    }

    public function test_bulk_move_skips_tasks_already_in_the_target_status(): void
    {
        $taskA = $this->makeTask();
        $taskB = $this->makeTask(['project_status_id' => $this->doing->id]);

        app(BulkMoveTasksService::class)->execute($this->owner, [$taskA->id, $taskB->id], $this->doing->id);

        Event::assertDispatchedTimes(TaskStatusChanged::class, 1);
    }

    public function test_due_date_change_dispatches_old_and_new(): void
    {
        $task = $this->makeTask(['end_date' => '2026-06-10 12:00:00']);

        app(UpdateTaskDatesService::class)->execute($this->owner, $task->id, null, '2026-06-12 12:00:00');

        Event::assertDispatched(TaskDueDateChanged::class, function (TaskDueDateChanged $event) {
            return $event->oldEndDate !== null
                && str_contains($event->oldEndDate, '2026-06-10')
                && str_contains((string) $event->newEndDate, '2026-06-12');
        });
    }

    public function test_unchanged_due_date_dispatches_nothing(): void
    {
        $task = $this->makeTask(['start_date' => null, 'end_date' => null]);

        app(UpdateTaskDatesService::class)->execute($this->owner, $task->id, null, null);

        Event::assertNotDispatched(TaskDueDateChanged::class);
    }

    public function test_bulk_due_date_clear_dispatches_only_for_tasks_that_had_a_due_date(): void
    {
        $withDue = $this->makeTask(['end_date' => '2026-06-10 12:00:00']);
        $withoutDue = $this->makeTask(['end_date' => null]);

        app(BulkUpdateTaskDueDateService::class)->execute($this->owner, [$withDue->id, $withoutDue->id], null);

        Event::assertDispatchedTimes(TaskDueDateChanged::class, 1);
        Event::assertDispatched(TaskDueDateChanged::class, fn (TaskDueDateChanged $event) => $event->newEndDate === null);
    }

    public function test_priority_change_dispatches_and_a_noop_does_not(): void
    {
        $task = $this->makeTask(['priority' => 'normal']);

        app(UpdateTaskPriorityService::class)->execute($this->owner, $task->id, 'high');
        app(UpdateTaskPriorityService::class)->execute($this->owner, $task->id, 'high');

        Event::assertDispatchedTimes(TaskPriorityChanged::class, 1);
        Event::assertDispatched(TaskPriorityChanged::class, function (TaskPriorityChanged $event) {
            return $event->oldPriority === 'normal' && $event->newPriority === 'high';
        });
    }

    public function test_bulk_priority_dispatches_per_actually_changed_task(): void
    {
        $changed = $this->makeTask(['priority' => 'normal']);
        $already = $this->makeTask(['priority' => 'high']);

        app(BulkUpdateTaskPriorityService::class)->execute($this->owner, [$changed->id, $already->id], 'high');

        Event::assertDispatchedTimes(TaskPriorityChanged::class, 1);
    }

    public function test_adding_attachments_dispatches_with_the_count_and_a_duplicate_does_not(): void
    {
        $task = $this->makeTask();
        $image = ['path' => 'attachments/a.png', 'url' => 'https://example.test/a.png'];

        app(AddTaskAttachmentsService::class)->execute($this->owner, $task->id, [$image]);
        app(AddTaskAttachmentsService::class)->execute($this->owner, $task->id, [$image]);

        Event::assertDispatchedTimes(TaskAttachmentAdded::class, 1);
        Event::assertDispatched(TaskAttachmentAdded::class, fn (TaskAttachmentAdded $event) => $event->attachedCount === 1);
    }

    public function test_a_rolled_back_bulk_move_fires_zero_events(): void
    {
        $mine = $this->makeTask();
        // A task on a foreign team — the actor has no update rights, so the
        // whole bulk transaction rolls back after $mine was already moved.
        $foreign = Task::factory()->create([
            'project_id' => ($foreignProject = Project::factory()->create())->id,
            'project_status_id' => ProjectStatus::factory()->create(['project_id' => $foreignProject->id])->id,
            'created_by_user_id' => User::factory()->create()->id,
        ]);

        try {
            app(BulkMoveTasksService::class)->execute($this->owner, [$mine->id, $foreign->id], $this->doing->id);
            $this->fail('Expected the bulk move to be rejected.');
        } catch (ValidationException|\Illuminate\Auth\Access\AuthorizationException) {
            // Either rejection path proves the rollback.
        }

        Event::assertNotDispatched(TaskStatusChanged::class);
        $this->assertSame($this->todo->id, $mine->fresh()->project_status_id);
    }

    public function test_toggle_complete_dispatches_the_status_event_through_delegation(): void
    {
        $task = $this->makeTask();

        app(ToggleTaskCompleteService::class)->execute($this->owner, $task->id);

        Event::assertDispatched(TaskStatusChanged::class, fn (TaskStatusChanged $event) => $event->becameDone());
    }

    public function test_creating_a_task_dispatches_assigned_per_assignee_plus_created_in_project(): void
    {
        app(CreateTaskService::class)->execute($this->owner, $this->project, [
            'name' => 'Fresh task',
            'project_status_id' => $this->todo->id,
            'assignee_user_ids' => [$this->member->id],
        ]);

        Event::assertDispatchedTimes(TaskAssigned::class, 1);
        Event::assertDispatched(TaskCreatedInProject::class, function (TaskCreatedInProject $event) {
            return $event->assigneeUserIds === [$this->member->id]
                && in_array($this->owner->id, $event->teamMemberIds, true)
                && in_array($this->member->id, $event->teamMemberIds, true)
                && $event->task->taskName === 'Fresh task';
        });
    }
}
