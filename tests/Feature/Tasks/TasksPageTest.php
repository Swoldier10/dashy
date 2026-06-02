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
        [$user, $project] = $this->memberProject();

        $this->actingAs($user)
            ->get(route('tasks.show', $project))
            ->assertOk()
            ->assertSee($project->name);
    }

    public function test_workspace_sidebar_shows_new_project_button_in_team_view(): void
    {
        [$user, $project] = $this->memberProject();

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

        $task = Task::where('name', 'Modal task')->firstOrFail();
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

    public function test_checkbox_toggles_task_selection(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
        ]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('toggleTaskSelection', $task->id)
            ->assertSet('selectedTaskIds', [$task->id])
            ->call('toggleTaskSelection', $task->id)
            ->assertSet('selectedTaskIds', []);
    }

    public function test_checkbox_selection_does_not_change_task_status(): void
    {
        // Regression: the row checkbox used to call toggleComplete and move
        // the task to a Done status. It now only selects for bulk actions.
        [$user, $project, $status] = $this->memberProject();
        ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Done->value,
            'name' => 'DONE',
            'position' => 0,
        ]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
        ]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('toggleTaskSelection', $task->id)
            ->assertHasNoErrors();

        $this->assertSame($status->id, $task->refresh()->project_status_id);
    }

    public function test_checkbox_renders_wired_to_selection_not_completion(): void
    {
        // Regression pin on the markup itself: the checkbox dispatches
        // toggleTaskSelection, and toggleComplete is gone from this page.
        [$user, $project, $status] = $this->memberProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
        ]);

        $this->actingAs($user);

        $html = Livewire::test('pages::tasks.show', ['project' => $project->id])->html();

        $this->assertStringContainsString('toggleTaskSelection('.$task->id.')', $html);
        $this->assertStringNotContainsString('toggleComplete', $html);
    }

    public function test_bulk_toolbar_is_hidden_until_a_task_is_selected(): void
    {
        // The toolbar must be in the DOM from the initial render (its Alpine
        // dropdown roots only initialize on page load — a morph-inserted @if
        // block leaves them unbound) and toggle visibility via the hidden class.
        [$user, $project, $status] = $this->memberProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
        ]);

        $this->actingAs($user);

        $component = Livewire::test('pages::tasks.show', ['project' => $project->id]);

        $this->assertStringContainsString('hidden', $this->bulkToolbarOpeningTag($component->html()));

        $component
            ->call('toggleTaskSelection', $task->id)
            ->assertSee(__(':count selected', ['count' => 1]));

        $this->assertStringNotContainsString('hidden', $this->bulkToolbarOpeningTag($component->html()));
    }

    private function bulkToolbarOpeningTag(string $html): string
    {
        return $this->openingTagFor($html, 'bulk-actions-toolbar');
    }

    /** Extract the opening tag of the element carrying the given data-test value. */
    private function openingTagFor(string $html, string $dataTest): string
    {
        $pos = strpos($html, 'data-test="'.$dataTest.'"');
        $this->assertNotFalse($pos, "Element [data-test={$dataTest}] missing from rendered HTML.");
        $start = strrpos(substr($html, 0, $pos), '<');
        $end = strpos($html, '>', $pos);

        return substr($html, $start, $end - $start);
    }

    public function test_popover_triggers_render_without_wire_click(): void
    {
        // Regression: an empty wire:click.stop on a popover trigger binds an
        // Alpine x-on:click.stop listener (Livewire 4 wildcard bridge) whose
        // stopPropagation() prevents the click from bubbling to the popover
        // wrapper's @click="toggle()" — the menu silently never opens. Popover
        // triggers must carry NO wire:click at all (the row actions menu is
        // the reference pattern).
        [$user, $project, $status] = $this->memberProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
        ]);

        $this->actingAs($user);

        $html = Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('toggleTaskSelection', $task->id)
            ->html();

        $triggers = [
            'bulk-status-trigger',
            'bulk-date-trigger',
            'bulk-priority-trigger',
            'status-trigger-'.$task->id,
            'date-trigger-'.$task->id,
            'priority-trigger-'.$task->id,
        ];

        foreach ($triggers as $dataTest) {
            $this->assertStringNotContainsString(
                'wire:click',
                $this->openingTagFor($html, $dataTest),
                "Popover trigger [{$dataTest}] must not bind wire:click — it swallows the toggle click."
            );
        }
    }

    public function test_bulk_set_status_moves_selected_tasks(): void
    {
        [$user, $project, $statusA] = $this->memberProject();
        $statusB = ProjectStatus::factory()->create(['project_id' => $project->id, 'position' => 1]);
        $a = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $statusA->id]);
        $b = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $statusA->id]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('toggleTaskSelection', $a->id)
            ->call('toggleTaskSelection', $b->id)
            ->call('bulkSetStatus', $statusB->id)
            ->assertHasNoErrors()
            ->assertSet('selectedTaskIds', []);

        $this->assertSame($statusB->id, $a->refresh()->project_status_id);
        $this->assertSame($statusB->id, $b->refresh()->project_status_id);
    }

    public function test_bulk_set_priority_updates_selected_tasks(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $a = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id, 'priority' => 'low']);
        $b = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id, 'priority' => 'normal']);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('toggleTaskSelection', $a->id)
            ->call('toggleTaskSelection', $b->id)
            ->call('bulkSetPriority', 'urgent')
            ->assertHasNoErrors()
            ->assertSet('selectedTaskIds', []);

        $this->assertSame('urgent', $a->refresh()->priority->value);
        $this->assertSame('urgent', $b->refresh()->priority->value);
    }

    public function test_bulk_set_due_date_applies_and_clears_conflicting_start(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $conflicting = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
            'start_date' => '2026-06-20',
            'end_date' => '2026-06-25',
        ]);
        $clean = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-03',
        ]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('toggleTaskSelection', $conflicting->id)
            ->call('toggleTaskSelection', $clean->id)
            ->call('bulkSetDueDate', '2026-06-10')
            ->assertHasNoErrors()
            ->assertSet('selectedTaskIds', []);

        $conflicting->refresh();
        $this->assertNull($conflicting->start_date, 'A start date after the new due date is cleared.');
        $this->assertSame('2026-06-10', $conflicting->end_date->toDateString());

        $clean->refresh();
        $this->assertSame('2026-06-01', $clean->start_date->toDateString());
        $this->assertSame('2026-06-10', $clean->end_date->toDateString());
    }

    public function test_bulk_clear_dates_nulls_dates(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-03',
        ]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('toggleTaskSelection', $task->id)
            ->call('bulkSetDueDate', null)
            ->assertHasNoErrors()
            ->assertSet('selectedTaskIds', []);

        $task->refresh();
        $this->assertNull($task->start_date);
        $this->assertNull($task->end_date);
    }

    public function test_bulk_archive_archives_selected_tasks(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $a = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id]);
        $b = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('toggleTaskSelection', $a->id)
            ->call('toggleTaskSelection', $b->id)
            ->call('bulkArchive')
            ->assertHasNoErrors()
            ->assertSet('selectedTaskIds', []);

        $this->assertTrue($a->refresh()->is_archived);
        $this->assertTrue($b->refresh()->is_archived);
    }

    public function test_bulk_delete_requires_confirmation_then_deletes(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $a = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id]);
        $b = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id]);

        $this->actingAs($user);

        $component = Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('toggleTaskSelection', $a->id)
            ->call('toggleTaskSelection', $b->id)
            ->call('confirmBulkDelete')
            ->assertDispatched('dashy-modal:open', name: 'confirm-bulk-delete');

        // Nothing deleted until the dialog is confirmed.
        $this->assertSame(2, Task::count());

        $component
            ->call('bulkDelete')
            ->assertHasNoErrors()
            ->assertDispatched('dashy-modal:close', name: 'confirm-bulk-delete')
            ->assertSet('selectedTaskIds', []);

        $this->assertSame(0, Task::count());
    }

    public function test_selection_is_pruned_when_a_selected_task_becomes_invisible(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $task = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id]);

        $this->actingAs($user);

        // Archiving a selected task via the row action hides it (showArchived
        // is off), so the task-list-changed refresh must drop it from selection.
        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('toggleTaskSelection', $task->id)
            ->assertSet('selectedTaskIds', [$task->id])
            ->call('archiveTask', $task->id)
            ->assertSet('selectedTaskIds', []);
    }

    public function test_bulk_actions_ignore_forged_ids_from_other_project(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $mine = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id, 'priority' => 'low']);
        $foreign = Task::factory()->create(['priority' => 'low']);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->set('selectedTaskIds', [$mine->id, $foreign->id])
            ->call('bulkSetPriority', 'urgent')
            ->assertHasNoErrors();

        $this->assertSame('urgent', $mine->refresh()->priority->value);
        $this->assertSame('low', $foreign->refresh()->priority->value, 'Forged ids outside the project are ignored.');
    }

    public function test_select_all_selects_every_visible_task(): void
    {
        [$user, $project, $statusA] = $this->memberProject();
        $statusB = ProjectStatus::factory()->create(['project_id' => $project->id, 'position' => 1]);
        $a = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $statusA->id]);
        $b = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $statusB->id]);

        $this->actingAs($user);

        $component = Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('toggleSelectAll');

        $this->assertEqualsCanonicalizing([$a->id, $b->id], $component->get('selectedTaskIds'));
        $this->assertStringContainsString(
            'aria-checked="true"',
            $this->openingTagFor($component->html(), 'select-all-tasks')
        );
    }

    public function test_select_all_deselects_when_everything_is_already_selected(): void
    {
        [$user, $project, $status] = $this->memberProject();
        Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id]);
        Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('toggleSelectAll')
            ->call('toggleSelectAll')
            ->assertSet('selectedTaskIds', []);
    }

    public function test_select_all_completes_a_partial_selection(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $a = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id]);
        $b = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id]);

        $this->actingAs($user);

        $component = Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('toggleTaskSelection', $a->id)
            ->call('toggleSelectAll');

        $this->assertEqualsCanonicalizing([$a->id, $b->id], $component->get('selectedTaskIds'));
    }

    public function test_select_all_excludes_archived_tasks_when_hidden(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $visible = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id]);
        Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id, 'is_archived' => true]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('toggleSelectAll')
            ->assertSet('selectedTaskIds', [$visible->id]);
    }

    public function test_select_all_is_a_no_op_and_hidden_when_there_are_no_tasks(): void
    {
        [$user, $project] = $this->memberProject();

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->assertDontSee('data-test="select-all-tasks"', false)
            ->call('toggleSelectAll')
            ->assertHasNoErrors()
            ->assertSet('selectedTaskIds', []);
    }

    public function test_bulk_actions_are_no_ops_on_empty_selection(): void
    {
        [$user, $project, $status] = $this->memberProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
            'priority' => 'low',
        ]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.show', ['project' => $project->id])
            ->call('bulkSetStatus', $status->id)
            ->call('bulkSetPriority', 'urgent')
            ->call('bulkSetDueDate', '2026-06-10')
            ->call('bulkArchive')
            ->call('confirmBulkDelete')
            ->assertNotDispatched('dashy-modal:open')
            ->call('bulkDelete')
            ->assertHasNoErrors();

        $task->refresh();
        $this->assertSame('low', $task->priority->value);
        $this->assertNull($task->end_date);
        $this->assertFalse($task->is_archived);
        $this->assertSame(1, Task::count());
    }
}
