<?php

namespace Tests\Feature\Tasks;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Livewire\TimeTracking\RunningTimerPill;
use App\Livewire\TimeTracking\TaskTimePanel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class TaskTimeTrackingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: Project, 2: Task}
     */
    private function setupMemberTask(): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->forProject($project, $status)->create();

        return [$user, $project, $task];
    }

    public function test_panel_renders_total_time_for_task(): void
    {
        [$user, , $task] = $this->setupMemberTask();
        TimeEntry::factory()->forTask($task)->forUser($user)->create(['duration_seconds' => 75 * 60]);

        Livewire::actingAs($user)
            ->test(TaskTimePanel::class, ['taskId' => $task->id])
            ->assertSee('1h 15m');
    }

    public function test_can_start_and_stop_a_timer(): void
    {
        [$user, , $task] = $this->setupMemberTask();
        Carbon::setTestNow('2026-05-11 12:00:00');

        $component = Livewire::actingAs($user)->test(TaskTimePanel::class, ['taskId' => $task->id]);
        $component->call('startTimer');

        $this->assertDatabaseHas('time_entries', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'ended_at' => null,
        ]);

        Carbon::setTestNow('2026-05-11 12:00:30');
        $component->call('stopTimer');

        $this->assertSame(0, TimeEntry::query()->whereNull('ended_at')->count());
        $this->assertSame(1, TimeEntry::query()->where('user_id', $user->id)->whereNotNull('duration_seconds')->count());

        Carbon::setTestNow();
    }

    public function test_can_log_manual_duration(): void
    {
        [$user, , $task] = $this->setupMemberTask();

        Livewire::actingAs($user)
            ->test(TaskTimePanel::class, ['taskId' => $task->id])
            ->set('manualDuration', '3h 20m')
            ->set('manualNotes', 'Pair session')
            ->call('logManual');

        $this->assertDatabaseHas('time_entries', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'duration_seconds' => 3 * 3600 + 20 * 60,
            'notes' => 'Pair session',
        ]);
    }

    public function test_invalid_manual_duration_triggers_validation_error(): void
    {
        [$user, , $task] = $this->setupMemberTask();

        Livewire::actingAs($user)
            ->test(TaskTimePanel::class, ['taskId' => $task->id])
            ->set('manualDuration', 'asdf')
            ->call('logManual')
            ->assertHasErrors(['duration']);

        $this->assertSame(0, TimeEntry::query()->count());
    }

    public function test_owner_can_delete_entry(): void
    {
        [$user, , $task] = $this->setupMemberTask();
        $entry = TimeEntry::factory()->forTask($task)->forUser($user)->create();

        Livewire::actingAs($user)
            ->test(TaskTimePanel::class, ['taskId' => $task->id])
            ->call('deleteEntry', $entry->id);

        $this->assertDatabaseMissing('time_entries', ['id' => $entry->id]);
    }

    public function test_can_edit_entry_duration(): void
    {
        [$user, , $task] = $this->setupMemberTask();
        $entry = TimeEntry::factory()->forTask($task)->forUser($user)->create([
            'duration_seconds' => 600,
        ]);

        Livewire::actingAs($user)
            ->test(TaskTimePanel::class, ['taskId' => $task->id])
            ->call('startEditing', $entry->id)
            ->set('editDuration', '45m')
            ->set('editNotes', 'updated')
            ->call('saveEntry', $entry->id);

        $entry->refresh();
        $this->assertSame(45 * 60, $entry->duration_seconds);
        $this->assertSame('updated', $entry->notes);
    }

    public function test_pill_hides_when_no_active_timer(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(RunningTimerPill::class)
            ->assertDontSee('data-test="running-timer-pill"', escape: false);
    }

    public function test_pill_shows_when_timer_is_running(): void
    {
        [$user, , $task] = $this->setupMemberTask();
        TimeEntry::factory()->forUser($user)->forTask($task)->running()->create();

        Livewire::actingAs($user)
            ->test(RunningTimerPill::class)
            ->assertSee('data-test="running-timer-pill"', escape: false)
            ->assertSee($task->name);
    }

    public function test_starting_a_new_timer_stops_the_previous_one(): void
    {
        [$user, , $taskA] = $this->setupMemberTask();
        $taskB = Task::factory()->forProject($taskA->project, $taskA->status)->create();
        Carbon::setTestNow('2026-05-11 09:00:00');
        TimeEntry::factory()->forUser($user)->forTask($taskA)->running()->create([
            'started_at' => Carbon::now()->subMinutes(10),
        ]);
        Carbon::setTestNow('2026-05-11 09:00:30');

        Livewire::actingAs($user)
            ->test(TaskTimePanel::class, ['taskId' => $taskB->id])
            ->call('startTimer');

        $this->assertSame(1, TimeEntry::query()->whereNull('ended_at')->count());
        $this->assertSame($taskB->id, TimeEntry::query()->whereNull('ended_at')->first()->task_id);

        Carbon::setTestNow();
    }

    public function test_task_list_includes_total_tracked_seconds_per_task(): void
    {
        [$user, $project, $task] = $this->setupMemberTask();
        TimeEntry::factory()->forTask($task)->forUser($user)->create(['duration_seconds' => 1800]);
        TimeEntry::factory()->forTask($task)->forUser($user)->create(['duration_seconds' => 600]);

        // Time tracking no longer renders in the row (UI matches the design in
        // task-row-card.blade.php), but the service must still load the rollup
        // onto every task so the detail drawer / panels can display it.
        $this->actingAs($user)
            ->get(route('tasks.show', $project))
            ->assertOk();

        $loaded = Task::query()->whereKey($task->id)->first();
        $loaded->total_tracked_seconds = app(\App\Domains\Tasks\Services\ListTasksForProjectService::class)
            ->execute($user, $project, false)
            ->firstWhere('id', $task->id)
            ->total_tracked_seconds;

        $this->assertSame(2400, (int) $loaded->total_tracked_seconds);
    }

    public function test_query_param_opens_task_drawer(): void
    {
        [$user, $project, $task] = $this->setupMemberTask();

        $this->actingAs($user)
            ->get(route('tasks.show', $project).'?task='.$task->id)
            ->assertOk()
            ->assertSee('data-test="task-detail-'.$task->id.'"', escape: false);
    }
}
