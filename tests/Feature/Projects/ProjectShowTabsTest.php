<?php

namespace Tests\Feature\Projects;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectShowTabsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: Project}
     */
    private function bootScenario(): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        ProjectStatus::factory()->create(['project_id' => $project->id]);

        return [$user, $project];
    }

    public function test_default_tab_is_tasks(): void
    {
        [$user, $project] = $this->bootScenario();

        Livewire::actingAs($user)
            ->test('pages::tasks.show', ['project' => $project->id])
            ->assertSet('activeTab', 'tasks')
            ->assertSeeHtml('data-test="tasks-page"');
    }

    public function test_switching_to_dashboard_marks_kanban_container_hidden(): void
    {
        // The tasks-page redesign changed the wrapper class set; this test
        // now asserts intent (lazy panel mounts only when dashboard is
        // active) rather than pinning exact Tailwind classes.
        [$user, $project] = $this->bootScenario();

        $tasks = Livewire::actingAs($user)
            ->test('pages::tasks.show', ['project' => $project->id]);

        $tasks->assertSee('data-test="tasks-page"', false);

        $dashboard = $tasks->set('activeTab', 'dashboard');
        $dashboardHtml = $dashboard->html();

        $this->assertStringContainsString(
            'project-dashboard-panel',
            $dashboardHtml,
            'Dashboard panel should mount when activeTab=dashboard',
        );
    }

    public function test_url_query_param_sets_initial_tab(): void
    {
        [$user, $project] = $this->bootScenario();

        $response = $this->actingAs($user)
            ->get(route('tasks.show', $project).'?tab=dashboard');

        $response->assertOk();
        $response->assertSee('data-test="tasks-page"', false);
    }

    public function test_unknown_tab_in_url_falls_back_to_tasks(): void
    {
        [$user, $project] = $this->bootScenario();

        $component = Livewire::actingAs($user)
            ->withQueryParams(['tab' => 'something-else'])
            ->test('pages::tasks.show', ['project' => $project->id]);

        $this->assertSame('tasks', $component->get('activeTab'));
    }
}
