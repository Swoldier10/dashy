<?php

namespace Tests\Feature\Tasks;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Livewire\Tasks\TaskDetailDrawer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TaskDetailDrawerComponentTest extends TestCase
{
    use RefreshDatabase;

    private function memberProject(): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id, 'name' => 'BACKLOG', 'position' => 0]);

        return [$user, $project, $status];
    }

    public function test_open_hydrates_form_fields(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
            'name' => 'Audit logs',
            'description' => 'A body',
            'priority' => 'high',
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-12',
        ]);

        Livewire::actingAs($user)
            ->test(TaskDetailDrawer::class)
            ->call('open', $task->id)
            ->assertSet('taskId', $task->id)
            ->assertSet('detailName', 'Audit logs')
            ->assertSet('detailDescription', 'A body')
            ->assertSet('detailStatusId', $status->id)
            ->assertSet('detailPriority', 'high')
            ->assertSet('detailStartDate', '2026-06-10T00:00')
            ->assertSet('detailEndDate', '2026-06-12T00:00')
            ->assertDispatched('dashy-modal:open', name: 'task-detail');
    }

    public function test_save_persists_name_description_and_dates(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
            'name' => 'Old name',
        ]);

        Livewire::actingAs($user)
            ->test(TaskDetailDrawer::class)
            ->call('open', $task->id)
            ->set('detailName', 'New name')
            ->set('detailDescription', 'Long body')
            ->set('detailStartDate', '2026-06-10')
            ->set('detailEndDate', '2026-06-15')
            ->call('saveTaskDetail')
            ->assertHasNoErrors();

        $task->refresh();
        $this->assertSame('New name', $task->name);
        $this->assertSame('Long body', $task->description);
        $this->assertSame('2026-06-10', $task->start_date->toDateString());
        $this->assertSame('2026-06-15', $task->end_date->toDateString());
    }

    public function test_save_persists_exact_datetime_from_picker(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
        ]);

        Livewire::actingAs($user)
            ->test(TaskDetailDrawer::class)
            ->call('open', $task->id)
            ->set('detailStartDate', '2026-06-10T09:30')
            ->set('detailEndDate', '2026-06-12T17:45')
            ->call('saveTaskDetail')
            ->assertHasNoErrors();

        $task->refresh();
        $this->assertSame('2026-06-10 09:30:00', $task->start_date->format('Y-m-d H:i:s'));
        $this->assertSame('2026-06-12 17:45:00', $task->end_date->format('Y-m-d H:i:s'));

        Livewire::actingAs($user)
            ->test(TaskDetailDrawer::class)
            ->call('open', $task->id)
            ->assertSet('detailStartDate', '2026-06-10T09:30')
            ->assertSet('detailEndDate', '2026-06-12T17:45');
    }

    public function test_status_change_persists_via_updated_hook(): void
    {
        [$user, $project, $statusA] = $this->memberProject();
        $statusB = ProjectStatus::factory()->create(['project_id' => $project->id, 'position' => 1]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $statusA->id,
        ]);

        Livewire::actingAs($user)
            ->test(TaskDetailDrawer::class)
            ->call('open', $task->id)
            ->set('detailStatusId', $statusB->id)
            ->assertHasNoErrors();

        $this->assertSame($statusB->id, $task->refresh()->project_status_id);
    }

    public function test_priority_change_persists_via_updated_hook(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
            'priority' => 'normal',
        ]);

        Livewire::actingAs($user)
            ->test(TaskDetailDrawer::class)
            ->call('open', $task->id)
            ->set('detailPriority', 'urgent')
            ->assertHasNoErrors();

        $this->assertSame('urgent', $task->refresh()->priority->value);
    }

    public function test_toggle_assignee_attaches_and_detaches(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $other = User::factory()->create();
        $project->team->members()->attach($other->id, ['role' => TeamRole::Member->value]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
        ]);

        $component = Livewire::actingAs($user)
            ->test(TaskDetailDrawer::class)
            ->call('open', $task->id);

        $component->call('toggleAssignee', $other->id);
        $this->assertTrue($task->refresh()->assignees->contains('id', $other->id));

        $component->call('toggleAssignee', $other->id);
        $this->assertFalse($task->refresh()->assignees->contains('id', $other->id));
    }

    public function test_delete_removes_task_closes_modal_and_dispatches(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
        ]);

        Livewire::actingAs($user)
            ->test(TaskDetailDrawer::class)
            ->call('open', $task->id)
            ->call('deleteTask')
            ->assertHasNoErrors()
            ->assertDispatched('dashy-modal:close', name: 'task-detail')
            ->assertDispatched('task-list-changed')
            ->assertDispatched('calendar-events-changed');

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_blocks_non_member_from_opening(): void
    {
        [, $project, $status] = $this->memberProject();
        $stranger = User::factory()->create();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
        ]);

        Livewire::actingAs($stranger)
            ->test(TaskDetailDrawer::class)
            ->call('open', $task->id)
            ->assertForbidden();
    }

    public function test_close_then_reopen_keeps_state_loaded(): void
    {
        // Regression: closeTaskDetail used to null out the task id, which
        // flipped the drawer template's @if($task)/@else branch and tore
        // out the nested time-panel Livewire component. The fix is two-pronged:
        //   1. closeTaskDetail is now a no-op so $taskId stays set.
        //   2. <livewire:task-time-panel> is rendered OUTSIDE @if($task),
        //      always mounted (taskId=0 when no task selected). The empty-
        //      state stub `task-time-panel-0` is in the initial render; the
        //      key swaps to `task-time-panel-<id>` after open.
        [$user, $project, $status] = $this->memberProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
            'name' => 'Sticky task',
        ]);

        Livewire::actingAs($user)
            ->test(TaskDetailDrawer::class)
            ->call('open', $task->id)
            ->assertSet('taskId', $task->id)
            ->assertSet('detailName', 'Sticky task')
            ->call('closeTaskDetail')
            ->assertSet('taskId', $task->id)
            ->assertSet('detailName', 'Sticky task')
            ->call('open', $task->id)
            ->assertSet('taskId', $task->id)
            ->assertSet('detailName', 'Sticky task')
            ->assertDispatched('dashy-modal:open', name: 'task-detail')
            ->assertHasNoErrors();
    }
}
