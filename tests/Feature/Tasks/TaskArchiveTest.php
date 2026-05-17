<?php

namespace Tests\Feature\Tasks;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TaskArchiveTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: User, 1: Project, 2: ProjectStatus} */
    private function memberProject(): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id, 'name' => 'BACKLOG', 'position' => 0]);

        return [$user, $project, $status];
    }

    public function test_archive_task_marks_field_and_hides_row_by_default(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
            'name' => 'Pickled Onions',
        ]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('archiveTask', $task->id)
            ->assertHasNoErrors()
            ->assertDontSee('Pickled Onions');

        $this->assertTrue($task->refresh()->is_archived);
    }

    public function test_toggle_archived_visibility_reveals_archived_row_with_badge(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $task = Task::factory()->archived()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
            'name' => 'Buried Treasure',
        ]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->assertDontSee('Buried Treasure')
            ->call('toggleArchivedVisibility')
            ->assertSet('showArchived', true)
            ->assertSee('Buried Treasure')
            ->assertSee(__('Archived'))
            ->assertSeeHtml('data-test="task-row-archived-badge-'.$task->id.'"');
    }

    public function test_unarchive_task_restores_visibility_in_default_view(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $task = Task::factory()->archived()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
            'name' => 'Resurrected Task',
        ]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->set('showArchived', true)
            ->call('unarchiveTask', $task->id)
            ->assertHasNoErrors()
            ->set('showArchived', false)
            ->assertSee('Resurrected Task');

        $this->assertFalse($task->refresh()->is_archived);
    }

    public function test_archived_query_param_boots_with_archived_visible(): void
    {
        [$user, $project, $status] = $this->memberProject();
        Task::factory()->archived()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
            'name' => 'Hidden In Plain Sight',
        ]);

        $this->actingAs($user)
            ->get(route('tasks.show', $project).'?archived=1')
            ->assertOk()
            ->assertSee('Hidden In Plain Sight');
    }

    public function test_action_bar_renders_inside_tasks_pane(): void
    {
        [$user, $project] = $this->memberProject();

        $this->actingAs($user)
            ->get(route('tasks.show', $project))
            ->assertOk()
            ->assertSeeHtml('data-test="tasks-action-bar"')
            ->assertSeeHtml('data-test="tasks-toggle-archived"');
    }
}
