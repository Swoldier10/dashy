<?php

namespace Tests\Feature\Projects;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Livewire\Projects\ProjectDashboardPanel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectDashboardPanelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: User, 1: Project, 2: Task}
     */
    private function bootScenario(): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->forProject($project, $status)->create();

        return [$user, $project, $task];
    }

    public function test_renders_widgets_and_chart_for_member(): void
    {
        Carbon::setTestNow('2026-05-15 12:00:00');

        [$user, $project, $task] = $this->bootScenario();
        TimeEntry::factory()->forTask($task)->forUser($user)->create([
            'started_at' => Carbon::parse('2026-05-10 09:00:00'),
            'ended_at' => Carbon::parse('2026-05-10 10:30:00'),
            'duration_seconds' => 90 * 60,
        ]);

        Livewire::actingAs($user)
            ->test(ProjectDashboardPanel::class, ['projectId' => $project->id])
            ->assertSee('1h 30m')
            ->assertSeeHtml('data-test="project-dashboard-chart"')
            ->assertSeeHtml('data-test="project-dashboard-total-month"');

        Carbon::setTestNow();
    }

    public function test_scope_toggle_filters_to_current_user(): void
    {
        Carbon::setTestNow('2026-05-15 12:00:00');

        [$user, $project, $task] = $this->bootScenario();
        $teammate = User::factory()->create();
        $project->team->members()->attach($teammate->id, ['role' => TeamRole::Member->value]);

        TimeEntry::factory()->forTask($task)->forUser($user)->create([
            'started_at' => Carbon::parse('2026-05-10 09:00:00'),
            'ended_at' => Carbon::parse('2026-05-10 10:00:00'),
            'duration_seconds' => 3600,
        ]);
        TimeEntry::factory()->forTask($task)->forUser($teammate)->create([
            'started_at' => Carbon::parse('2026-05-10 11:00:00'),
            'ended_at' => Carbon::parse('2026-05-10 13:00:00'),
            'duration_seconds' => 7200,
        ]);

        $component = Livewire::actingAs($user)
            ->test(ProjectDashboardPanel::class, ['projectId' => $project->id]);

        $this->assertSame(3600, $component->instance()->totalAllTimeSeconds);

        $component->call('setScope', 'team');
        $this->assertSame(10800, $component->instance()->totalAllTimeSeconds);

        Carbon::setTestNow();
    }

    public function test_invalid_scope_is_ignored(): void
    {
        [$user, $project] = $this->bootScenario();

        $component = Livewire::actingAs($user)
            ->test(ProjectDashboardPanel::class, ['projectId' => $project->id])
            ->call('setScope', 'everyone');

        $this->assertSame('me', $component->get('scope'));
    }

    public function test_previous_and_next_month_navigation(): void
    {
        Carbon::setTestNow('2026-05-15 12:00:00');

        [$user, $project] = $this->bootScenario();

        $component = Livewire::actingAs($user)
            ->test(ProjectDashboardPanel::class, ['projectId' => $project->id]);

        $this->assertSame('2026-05-01', $component->get('monthAnchor'));

        $component->call('previousMonth');
        $this->assertSame('2026-04-01', $component->get('monthAnchor'));

        $component->call('nextMonth')->call('nextMonth');
        $this->assertSame('2026-06-01', $component->get('monthAnchor'));

        $component->call('goToCurrentMonth');
        $this->assertSame('2026-05-01', $component->get('monthAnchor'));

        Carbon::setTestNow();
    }

    public function test_renders_money_under_total_when_team_has_hourly_rate(): void
    {
        Carbon::setTestNow('2026-05-15 12:00:00');

        [$user, $project, $task] = $this->bootScenario();
        $project->team->update(['hourly_rate' => '100.00', 'currency' => 'CHF']);

        TimeEntry::factory()->forTask($task)->forUser($user)->create([
            'started_at' => Carbon::parse('2026-05-10 09:00:00'),
            'ended_at' => Carbon::parse('2026-05-10 10:30:00'),
            'duration_seconds' => 90 * 60,
        ]);

        Livewire::actingAs($user)
            ->test(ProjectDashboardPanel::class, ['projectId' => $project->id])
            ->assertSee('1h 30m')
            ->assertSee('150.00 CHF')
            ->assertSeeHtml('data-test="project-dashboard-total-money"');

        Carbon::setTestNow();
    }

    public function test_hides_money_when_team_has_no_hourly_rate(): void
    {
        Carbon::setTestNow('2026-05-15 12:00:00');

        [$user, $project, $task] = $this->bootScenario();

        TimeEntry::factory()->forTask($task)->forUser($user)->create([
            'started_at' => Carbon::parse('2026-05-10 09:00:00'),
            'ended_at' => Carbon::parse('2026-05-10 10:00:00'),
            'duration_seconds' => 3600,
        ]);

        Livewire::actingAs($user)
            ->test(ProjectDashboardPanel::class, ['projectId' => $project->id])
            ->assertDontSeeHtml('data-test="project-dashboard-total-money"');

        Carbon::setTestNow();
    }

    public function test_time_entries_updated_event_triggers_refresh(): void
    {
        Carbon::setTestNow('2026-05-15 12:00:00');

        [$user, $project, $task] = $this->bootScenario();

        $component = Livewire::actingAs($user)
            ->test(ProjectDashboardPanel::class, ['projectId' => $project->id]);

        $this->assertSame(0, $component->instance()->totalAllTimeSeconds);

        TimeEntry::factory()->forTask($task)->forUser($user)->create([
            'started_at' => Carbon::parse('2026-05-12 09:00:00'),
            'ended_at' => Carbon::parse('2026-05-12 10:00:00'),
            'duration_seconds' => 3600,
        ]);

        $component->dispatch('time-entries-updated', taskId: $task->id);

        $this->assertSame(3600, $component->instance()->totalAllTimeSeconds);

        Carbon::setTestNow();
    }
}
