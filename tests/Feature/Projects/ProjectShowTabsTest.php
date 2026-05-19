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

    public function test_tab_buttons_emit_wire_click_to_sync_with_livewire(): void
    {
        // Regression: <x-dashy.tabs> previously left wire-model in $attributes
        // instead of declaring it as a prop, so <x-dashy.tab> read $wireModel
        // as null via @aware and never emitted wire:click. Clicks then updated
        // Alpine local state but not Livewire, so the pill highlight changed
        // while the content stayed on the previous tab.
        [$user, $project] = $this->bootScenario();

        $html = Livewire::actingAs($user)
            ->test('pages::tasks.show', ['project' => $project->id])
            ->html();

        $this->assertStringContainsString(
            'wire:click="$set(\'activeTab\', \'tasks\')"',
            $html,
            'Tasks tab button must emit wire:click that updates the Livewire activeTab.',
        );
        $this->assertStringContainsString(
            'wire:click="$set(\'activeTab\', \'dashboard\')"',
            $html,
            'Dashboard tab button must emit wire:click that updates the Livewire activeTab.',
        );
    }

    public function test_tab_buttons_bind_aria_selected_to_wire_state(): void
    {
        // Regression: the previous implementation bound :aria-selected to an
        // Alpine-local `value` variable mirrored from $defaultValue at init.
        // After wire:navigate the Alpine value persisted from the old page and
        // the pill ended up "a step behind" the server's $activeTab. The new
        // binding reads $wire['activeTab'] directly so any change to the
        // Livewire property — click, navigate, programmatic — updates the
        // pill on the next reactive tick.
        [$user, $project] = $this->bootScenario();

        $html = Livewire::actingAs($user)
            ->test('pages::tasks.show', ['project' => $project->id])
            ->html();

        $this->assertStringContainsString(
            ":aria-selected=\"\$wire['activeTab'] === 'tasks'\"",
            $html,
            'Tasks tab must reactively bind aria-selected to $wire.activeTab.',
        );
        $this->assertStringContainsString(
            ":aria-selected=\"\$wire['activeTab'] === 'dashboard'\"",
            $html,
            'Dashboard tab must reactively bind aria-selected to $wire.activeTab.',
        );
    }

    public function test_default_tab_ssr_marks_tasks_selected(): void
    {
        [$user, $project] = $this->bootScenario();

        $html = Livewire::actingAs($user)
            ->test('pages::tasks.show', ['project' => $project->id])
            ->html();

        // With $activeTab='tasks' the SSR pill must be on Tasks, not Dashboard.
        $this->assertMatchesRegularExpression(
            '/<button[^>]*aria-selected="true"[^>]*>\s*(?:<[^>]*>\s*)*<span>Tasks<\/span>/',
            $html,
            'Tasks pill must be aria-selected="true" in the SSR HTML.',
        );
        $this->assertMatchesRegularExpression(
            '/<button[^>]*aria-selected="false"[^>]*>\s*(?:<[^>]*>\s*)*<span>Dashboard<\/span>/',
            $html,
            'Dashboard pill must be aria-selected="false" in the SSR HTML.',
        );
    }

    public function test_set_active_tab_flips_ssr_pill(): void
    {
        // Catches the "step behind" symptom at the HTML layer: after the
        // server's activeTab flips, the next render must SSR the Dashboard
        // pill as selected.
        [$user, $project] = $this->bootScenario();

        $html = Livewire::actingAs($user)
            ->test('pages::tasks.show', ['project' => $project->id])
            ->set('activeTab', 'dashboard')
            ->html();

        $this->assertMatchesRegularExpression(
            '/<button[^>]*aria-selected="true"[^>]*>\s*(?:<[^>]*>\s*)*<span>Dashboard<\/span>/',
            $html,
            'After setting activeTab=dashboard, Dashboard pill must be SSR-selected.',
        );
        $this->assertMatchesRegularExpression(
            '/<button[^>]*aria-selected="false"[^>]*>\s*(?:<[^>]*>\s*)*<span>Tasks<\/span>/',
            $html,
            'After setting activeTab=dashboard, Tasks pill must NOT be SSR-selected.',
        );
    }
}
