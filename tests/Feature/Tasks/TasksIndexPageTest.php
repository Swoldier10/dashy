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

class TasksIndexPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_workspace_chrome_with_team_chips_and_workspace_sidebar(): void
    {
        [$user, $team, $project] = $this->seedScenario();
        Task::factory()->create(['project_id' => $project->id]);

        $response = $this->actingAs($user)->get(route('tasks'));

        $response->assertOk();
        $response->assertSee('data-test="tasks-top-bar"', escape: false);
        $response->assertSee('data-test="tasks-team-chips"', escape: false);
        $response->assertSee('data-test="tasks-team-chip-everything"', escape: false);
        $response->assertSee('data-test="tasks-team-chip-'.$team->id.'"', escape: false);
        $response->assertSee('data-test="workspace-sidebar"', escape: false);
        $response->assertSee('data-test="workspace-sidebar-everything"', escape: false);
        $response->assertSee('data-test="workspace-sidebar-project-'.$project->id.'"', escape: false);
        $response->assertSee('data-test="page-heading"', escape: false);
        $response->assertSee('data-test="status-summary"', escape: false);
        $response->assertSee('data-test="status-summary-totals"', escape: false);
    }

    public function test_renders_task_rows_with_project_pill_and_no_status_pill(): void
    {
        [$user, , $project, $status] = $this->seedScenario();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
            'name' => 'Aggregated Task',
        ]);

        $response = $this->actingAs($user)->get(route('tasks'));

        $response->assertOk();
        $response->assertSee('Aggregated Task');
        $response->assertSee('data-test="task-row-'.$task->id.'"', escape: false);
        $response->assertSee('data-test="task-row-project-'.$task->id.'"', escape: false);
        // Aggregator rows render a drag handle (sortable) and omit the per-row checkbox.
        $response->assertDontSee('data-test="task-checkbox-'.$task->id.'"', escape: false);
        $response->assertSee('data-test="aggregator-sortable-', escape: false);
        $response->assertSee('class="task-drag-handle', escape: false);
    }

    public function test_team_param_filters_to_that_team(): void
    {
        $user = User::factory()->create();
        [$teamA, $projectA] = $this->seedTeamForUser($user);
        [$teamB, $projectB] = $this->seedTeamForUser($user);

        $statusA = ProjectStatus::factory()->create(['project_id' => $projectA->id]);
        $statusB = ProjectStatus::factory()->create(['project_id' => $projectB->id]);
        Task::factory()->create(['project_id' => $projectA->id, 'project_status_id' => $statusA->id, 'name' => 'TaskA1']);
        Task::factory()->create(['project_id' => $projectB->id, 'project_status_id' => $statusB->id, 'name' => 'TaskB1']);

        $this->actingAs($user)
            ->get(route('tasks', ['team' => $teamA->id]))
            ->assertOk()
            ->assertSee('TaskA1')
            ->assertDontSee('TaskB1');
    }

    public function test_checkbox_toggles_task_to_done_via_service(): void
    {
        [$user, , $project, $status] = $this->seedScenario();
        $done = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Done->value,
        ]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
        ]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.index')
            ->call('toggleComplete', $task->id)
            ->assertHasNoErrors();

        $this->assertSame($done->id, $task->refresh()->project_status_id);
    }

    public function test_archived_query_param_includes_archived_rows(): void
    {
        [$user, , $project, $status] = $this->seedScenario();
        Task::factory()->archived()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
            'name' => 'Hidden Archive',
        ]);

        $this->actingAs($user)
            ->get(route('tasks').'?archived=1')
            ->assertOk()
            ->assertSee('Hidden Archive');
    }

    public function test_task_query_param_hydrates_detail_drawer(): void
    {
        [$user, , $project, $status] = $this->seedScenario();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
        ]);

        $this->actingAs($user)
            ->get(route('tasks').'?task='.$task->id)
            ->assertOk();
    }

    public function test_aggregator_reorder_persists_position_for_same_status_peers(): void
    {
        [$user, , $project, $status] = $this->seedScenario();
        $a = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id, 'position' => 0, 'name' => 'A']);
        $b = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id, 'position' => 1, 'name' => 'B']);
        $c = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $status->id, 'position' => 2, 'name' => 'C']);

        $this->actingAs($user);

        // Move C to front (drag handler post-drop order: C, A, B).
        Livewire::test('pages::tasks.index')
            ->call('aggregatorReorderBucket', $c->id, [$c->id, $a->id, $b->id])
            ->assertHasNoErrors();

        $this->assertSame(0, $c->refresh()->position);
        $this->assertSame(1, $a->refresh()->position);
        $this->assertSame(2, $b->refresh()->position);
    }

    public function test_aggregator_move_between_buckets_resolves_target_status_in_same_project(): void
    {
        [$user, , $project, $statusFrom] = $this->seedScenario();
        $statusTo = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'name' => 'In progress',
            'category' => ProjectStatusCategory::Active->value,
        ]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $statusFrom->id,
            'name' => 'Dragged',
        ]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.index')
            ->call('aggregatorMoveBetweenBuckets', $task->id, 'in-progress', [], [$task->id])
            ->assertHasNoErrors();

        $this->assertSame($statusTo->id, $task->refresh()->project_status_id);
    }

    public function test_aggregator_move_aborts_when_target_bucket_has_no_matching_status_in_project(): void
    {
        [$user, , $project, $statusFrom] = $this->seedScenario();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $statusFrom->id,
        ]);

        $this->actingAs($user);

        Livewire::test('pages::tasks.index')
            ->call('aggregatorMoveBetweenBuckets', $task->id, 'nonexistent-bucket', [], [$task->id])
            ->assertHasNoErrors();

        $this->assertSame($statusFrom->id, $task->refresh()->project_status_id);
    }

    public function test_new_task_button_renders_with_cocoa_variant(): void
    {
        [$user] = $this->seedScenario();

        $this->actingAs($user)
            ->get(route('tasks'))
            ->assertOk()
            ->assertSee('data-test="tasks-header-add"', escape: false)
            ->assertSee('dashy-btn--cocoa', escape: false);
    }

    /** @return array{0: User, 1: Team, 2: Project, 3: ProjectStatus} */
    private function seedScenario(): array
    {
        $user = User::factory()->create();
        [$team, $project] = $this->seedTeamForUser($user);
        $status = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => ProjectStatusCategory::Active->value,
        ]);

        return [$user, $team, $project, $status];
    }

    /** @return array{0: Team, 1: Project} */
    private function seedTeamForUser(User $user): array
    {
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        return [$team, $project];
    }
}
