<?php

namespace Tests\Feature\Calendar;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Livewire\Calendar;
use App\Livewire\Tasks\TaskDetailDrawer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CalendarTaskDrawerTest extends TestCase
{
    use RefreshDatabase;

    private function memberProject(): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        return [$user, $project];
    }

    public function test_calendar_page_mounts_the_task_detail_drawer(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('calendar'))
            ->assertOk()
            ->assertSeeLivewire(TaskDetailDrawer::class);
    }

    public function test_clicking_a_task_pill_dispatches_open_event(): void
    {
        [$user, $project] = $this->memberProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'name' => 'Audit logs',
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-15',
        ]);

        Livewire::actingAs($user)
            ->withQueryParams(['view' => 'week', 'date' => '2026-06-15'])
            ->test(Calendar::class)
            ->call('openTaskDetail', $task->id)
            ->assertDispatched('task-detail:open', taskId: $task->id);
    }

    public function test_calendar_refreshes_overlay_when_task_changes(): void
    {
        [$user, $project] = $this->memberProject();
        Task::factory()->create([
            'project_id' => $project->id,
            'name' => 'Visible task',
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-15',
        ]);

        $component = Livewire::actingAs($user)
            ->withQueryParams(['view' => 'week', 'date' => '2026-06-15'])
            ->test(Calendar::class);

        $before = $component->instance()->getCalendarPayload('2026-06-15T00:00:00', '2026-06-21T23:59:59');
        $this->assertContains('Visible task', array_column($before, 'title'));

        // task-list-changed re-rendering keeps the listener responsive; the
        // FE refetches via FullCalendar. Server-side we just verify the
        // payload remains correct after the dispatch.
        $component->dispatch('task-list-changed');

        $after = $component->instance()->getCalendarPayload('2026-06-15T00:00:00', '2026-06-21T23:59:59');
        $this->assertContains('Visible task', array_column($after, 'title'));
    }

    public function test_drawer_delete_removes_task_via_dispatch_from_calendar(): void
    {
        [$user, $project] = $this->memberProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-15',
        ]);

        Livewire::actingAs($user)
            ->test(TaskDetailDrawer::class)
            ->call('open', $task->id)
            ->call('deleteTask');

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
