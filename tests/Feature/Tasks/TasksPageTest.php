<?php

namespace Tests\Feature\Tasks;

use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TasksPageTest extends TestCase
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

    public function test_route_resolves_for_team_member(): void
    {
        [$user, $project, ] = $this->memberProject();

        $this->actingAs($user)
            ->get(route('tasks.show', $project))
            ->assertOk()
            ->assertSee($project->name);
    }

    public function test_workspace_sidebar_shows_new_project_button_in_team_view(): void
    {
        [$user, $project, ] = $this->memberProject();

        $this->actingAs($user)
            ->get(route('tasks.show', $project))
            ->assertOk()
            ->assertSee('workspace-sidebar-create-project-'.$project->team_id, false)
            ->assertSee('open-create-project', false);
    }

    public function test_route_403_for_non_member(): void
    {
        $stranger = User::factory()->create();
        $project = Project::factory()->create();

        $this->actingAs($stranger)
            ->get(route('tasks.show', $project))
            ->assertForbidden();
    }

    public function test_route_404_for_unknown_project(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/tasks/999999')
            ->assertNotFound();
    }

    public function test_renders_tasks_grouped_by_status(): void
    {
        [$user, $project, $status] = $this->memberProject();
        Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
            'name' => 'Audit logs',
        ]);

        $this->actingAs($user)
            ->get(route('tasks.show', $project))
            ->assertSee('BACKLOG')
            ->assertSee('Audit logs');
    }

    public function test_status_groups_render_in_modal_category_order(): void
    {
        [$user, $project] = $this->memberProject();

        // memberProject seeds one NotStarted status ("BACKLOG"). Add the other
        // categories out of order so we can prove the page reorders them.
        ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Closed->value,
            'name' => 'CLOSED-X',
            'position' => 0,
        ]);
        ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Done->value,
            'name' => 'DONE-X',
            'position' => 0,
        ]);
        ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Active->value,
            'name' => 'ACTIVE-X',
            'position' => 0,
        ]);
        ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::NotStarted->value,
            'name' => 'NOT-STARTED-EXTRA',
            'position' => 1,
        ]);

        // Groups render in reverse-enum order (most-advanced first):
        // closed → done → active → not started. Within a category the
        // relation's position order is preserved (stable sort).
        $this->actingAs($user)
            ->get(route('tasks.show', $project))
            ->assertSeeInOrder([
                'CLOSED-X',
                'DONE-X',
                'ACTIVE-X',
                'BACKLOG',
                'NOT-STARTED-EXTRA',
            ]);
    }

    public function test_renders_empty_state_when_no_statuses(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        $this->actingAs($user)
            ->get(route('tasks.show', $project))
            ->assertSee('No statuses yet');
    }

    public function test_inline_status_change_persists(): void
    {
        [$user, $project, $statusA] = $this->memberProject();
        $statusB = ProjectStatus::factory()->create(['project_id' => $project->id, 'position' => 1]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $statusA->id,
        ]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('updateStatus', $task->id, $statusB->id)
            ->assertHasNoErrors();

        $this->assertSame($statusB->id, $task->refresh()->project_status_id);
    }

    public function test_inline_priority_change_persists(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
            'priority' => 'normal',
        ]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('updatePriority', $task->id, 'urgent')
            ->assertHasNoErrors();

        $this->assertSame('urgent', $task->refresh()->priority->value);
    }

    public function test_inline_date_change_persists(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
        ]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('updateDates', $task->id, '2026-05-10', '2026-05-15')
            ->assertHasNoErrors();

        $this->assertSame('2026-05-15', $task->refresh()->end_date->toDateString());
    }

    public function test_assign_and_unassign_user(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $other = User::factory()->create();
        $project->team->members()->attach($other->id, ['role' => TeamRole::Member->value]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
        ]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('toggleAssignee', $task->id, $other->id);
        $this->assertTrue($task->refresh()->assignees->contains('id', $other->id));

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('toggleAssignee', $task->id, $other->id);
        $this->assertFalse($task->refresh()->assignees->contains('id', $other->id));
    }

    public function test_open_create_task_modal_defaults_to_first_status(): void
    {
        [$user, $project, $status] = $this->memberProject();

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('openCreateTask')
            ->assertSet('createOpen', true)
            ->assertSet('createStatusId', $status->id);
    }

    public function test_create_task_from_modal_persists_with_assignees(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $other = User::factory()->create();
        $project->team->members()->attach($other->id, ['role' => TeamRole::Member->value]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('openCreateTask', $status->id)
            ->set('createName', 'Modal task')
            ->set('createDescription', 'Body')
            ->set('createPriority', 'high')
            ->set('createStartDate', '2026-05-10')
            ->set('createEndDate', '2026-05-15')
            ->call('toggleCreateAssignee', $other->id)
            ->call('submitCreateTask')
            ->assertHasNoErrors()
            ->assertSet('createOpen', false);

        $task = \App\Domains\Tasks\Models\Task::where('name', 'Modal task')->firstOrFail();
        $this->assertSame($status->id, $task->project_status_id);
        $this->assertSame('high', $task->priority->value);
        $this->assertSame('2026-05-15', $task->end_date->toDateString());
        $this->assertTrue($task->assignees->contains('id', $other->id));
    }

    public function test_open_task_detail_dispatches_event_to_child_drawer(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
        ]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('openTaskDetail', $task->id)
            ->assertDispatched('task-detail:open', taskId: $task->id);
    }

    public function test_rendered_html_has_balanced_morph_block_markers(): void
    {
        // Regression: Livewire 4's precompiler injects
        //   <!--[if BLOCK]><![endif]--> / <!--[if ENDBLOCK]><![endif]-->
        // HTML comments around every @if/@unless/@error/etc. directive so its
        // morph engine can patch conditional blocks. The matching algorithm
        // (livewire.js:13494-13530) walks siblings looking for the matching
        // ENDBLOCK by counting nested-if depth — if a stray BLOCK exists with
        // no matching ENDBLOCK, the walk runs off the end, returns undefined
        // for fromBlockEnd, and the next call to `Block.appendChild` blows up
        // with `TypeError: Cannot read properties of null (reading 'before')`.
        //
        // We hit this because `components/dashy/input.blade.php` had `@error`
        // inside a PHP comment in its @props block — the precompiler matched
        // it as an opening directive and injected an orphan BLOCK. Guard the
        // page render against any future regression of this kind by counting
        // BLOCK vs ENDBLOCK comments in the rendered HTML.
        [$user, $project, $status] = $this->memberProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
        ]);

        $this->actingAs($user);

        $html = Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('openTaskDetail', $task->id)
            ->html();

        $blockOpens = substr_count($html, '<!--[if BLOCK]><![endif]-->');
        $blockCloses = substr_count($html, '<!--[if ENDBLOCK]><![endif]-->');

        $this->assertSame(
            $blockOpens,
            $blockCloses,
            "Rendered HTML has {$blockOpens} BLOCK starts vs {$blockCloses} BLOCK ends. ".
            'A common cause is an `@`-prefixed Blade directive (@if/@error/...) sitting inside a PHP `//` comment '.
            'in a component template — Livewire\'s precompiler treats it as a real directive and injects an orphan marker.'
        );
    }

    public function test_drawer_delete_removes_task(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
        ]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('deleteTask', $task->id)
            ->assertHasNoErrors();

        $this->assertSame(0, Task::count());
    }

    public function test_reorder_within_status_persists_positions(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $a = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id, 'position' => 0]);
        $b = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id, 'position' => 1]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('reorderTasks', $status->id, [$b->id, $a->id])
            ->assertHasNoErrors();

        $this->assertSame(0, $b->refresh()->position);
        $this->assertSame(1, $a->refresh()->position);
    }

    public function test_drag_to_other_status_changes_status_id(): void
    {
        [$user, $project, $statusA] = $this->memberProject();
        $statusB = ProjectStatus::factory()->create(['project_id' => $project->id, 'position' => 1]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $statusA->id,
            'position' => 0,
        ]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('moveTask', $task->id, $statusB->id, [], [$task->id])
            ->assertHasNoErrors();

        $this->assertSame($statusB->id, $task->refresh()->project_status_id);
    }

    public function test_reorder_ignores_forged_ids_from_other_project(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $foreign = Task::factory()->create();
        $mine = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id, 'position' => 5]);
        $foreignBefore = $foreign->position;

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('reorderTasks', $status->id, [$foreign->id, $mine->id])
            ->assertHasNoErrors();

        $this->assertSame($foreignBefore, $foreign->refresh()->position);
        $this->assertSame(1, $mine->refresh()->position);
    }

    public function test_each_rendered_task_has_exactly_one_data_task_id_attribute(): void
    {
        // Regression: a duplicate data-task-id on a nested div made SortableJS
        // pick the wrong (non-direct-child) element via closest(), silently
        // breaking drag-and-drop. The sortable selector matches direct children
        // of the container only, so the attribute must appear once per task.
        [$user, $project, $status] = $this->memberProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
        ]);

        $this->actingAs($user);

        $html = Livewire::test('pages::tasks.show', ['project' => $project->id])->html();

        $occurrences = substr_count($html, 'data-task-id="'.$task->id.'"');
        $this->assertSame(1, $occurrences, 'Expected exactly one data-task-id per task in rendered HTML.');
    }
}
