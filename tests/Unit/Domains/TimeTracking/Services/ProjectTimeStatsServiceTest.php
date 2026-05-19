<?php

namespace Tests\Unit\Domains\TimeTracking\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Domains\TimeTracking\Services\ProjectTimeStatsService;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTimeStatsServiceTest extends TestCase
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

    public function test_back_fills_every_day_of_the_month_with_zero(): void
    {
        [$user, $project, $task] = $this->bootScenario();

        TimeEntry::factory()->forTask($task)->forUser($user)->create([
            'started_at' => CarbonImmutable::parse('2026-05-10 09:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-10 10:00:00'),
            'duration_seconds' => 3600,
        ]);

        $result = app(ProjectTimeStatsService::class)->dailyHoursForMonth(
            $user,
            $project->id,
            CarbonImmutable::parse('2026-05-15'),
        );

        $this->assertCount(31, $result);
        $this->assertSame(3600, $result['2026-05-10']);
        $this->assertSame(0, $result['2026-05-01']);
        $this->assertSame(0, $result['2026-05-31']);
        $this->assertArrayNotHasKey('2026-04-30', $result);
        $this->assertArrayNotHasKey('2026-06-01', $result);
    }

    public function test_excludes_entries_from_adjacent_months(): void
    {
        [$user, $project, $task] = $this->bootScenario();

        TimeEntry::factory()->forTask($task)->forUser($user)->create([
            'started_at' => CarbonImmutable::parse('2026-04-30 23:30:00'),
            'ended_at' => CarbonImmutable::parse('2026-04-30 23:45:00'),
            'duration_seconds' => 900,
        ]);

        $result = app(ProjectTimeStatsService::class)->dailyHoursForMonth(
            $user,
            $project->id,
            CarbonImmutable::parse('2026-05-01'),
        );

        $this->assertSame(0, array_sum($result));
    }

    public function test_non_member_cannot_read_daily_hours(): void
    {
        [, $project] = $this->bootScenario();
        $stranger = User::factory()->create();

        $this->expectException(AuthorizationException::class);
        app(ProjectTimeStatsService::class)->dailyHoursForMonth(
            $stranger,
            $project->id,
            CarbonImmutable::parse('2026-05-01'),
        );
    }

    public function test_non_member_cannot_read_total(): void
    {
        [, $project] = $this->bootScenario();
        $stranger = User::factory()->create();

        $this->expectException(AuthorizationException::class);
        app(ProjectTimeStatsService::class)->totalSecondsForProject($stranger, $project->id);
    }

    public function test_daily_entry_counts_back_fill_every_day_and_count_correctly(): void
    {
        [$user, $project, $task] = $this->bootScenario();

        TimeEntry::factory()->forTask($task)->forUser($user)->create([
            'started_at' => CarbonImmutable::parse('2026-05-10 09:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-10 10:00:00'),
            'duration_seconds' => 3600,
        ]);
        TimeEntry::factory()->forTask($task)->forUser($user)->create([
            'started_at' => CarbonImmutable::parse('2026-05-10 14:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-10 15:30:00'),
            'duration_seconds' => 5400,
        ]);
        TimeEntry::factory()->forTask($task)->forUser($user)->create([
            'started_at' => CarbonImmutable::parse('2026-05-20 09:00:00'),
            'ended_at' => CarbonImmutable::parse('2026-05-20 10:00:00'),
            'duration_seconds' => 3600,
        ]);

        $result = app(ProjectTimeStatsService::class)->dailyEntryCountsForMonth(
            $user,
            $project->id,
            CarbonImmutable::parse('2026-05-15'),
        );

        $this->assertCount(31, $result, 'Every day in May must be present so chart bars align with sums.');
        $this->assertSame(2, $result['2026-05-10']);
        $this->assertSame(1, $result['2026-05-20']);
        $this->assertSame(0, $result['2026-05-01']);
        $this->assertSame(0, $result['2026-05-31']);
    }

    public function test_non_member_cannot_read_daily_entry_counts(): void
    {
        [, $project] = $this->bootScenario();
        $stranger = User::factory()->create();

        $this->expectException(AuthorizationException::class);
        app(ProjectTimeStatsService::class)->dailyEntryCountsForMonth(
            $stranger,
            $project->id,
            CarbonImmutable::parse('2026-05-01'),
        );
    }

    public function test_total_seconds_for_project_returns_sum(): void
    {
        [$user, $project, $task] = $this->bootScenario();

        TimeEntry::factory()->forTask($task)->forUser($user)->create(['duration_seconds' => 600]);
        TimeEntry::factory()->forTask($task)->forUser($user)->create(['duration_seconds' => 1200]);

        $this->assertSame(1800, app(ProjectTimeStatsService::class)->totalSecondsForProject($user, $project->id));
    }

    public function test_billing_rate_returns_rate_and_currency_when_team_has_both(): void
    {
        [$user, $project] = $this->bootScenario();
        $project->team->update(['hourly_rate' => '125.50', 'currency' => 'CHF']);

        $result = app(ProjectTimeStatsService::class)->billingRateForProject($user, $project->id);

        $this->assertSame(125.50, $result['rate']);
        $this->assertSame('CHF', $result['currency']);
    }

    public function test_billing_rate_returns_null_when_team_has_no_rate(): void
    {
        [$user, $project] = $this->bootScenario();

        $this->assertNull(
            app(ProjectTimeStatsService::class)->billingRateForProject($user, $project->id),
        );
    }

    public function test_billing_rate_returns_null_when_only_currency_is_set(): void
    {
        [$user, $project] = $this->bootScenario();
        $project->team->update(['hourly_rate' => null, 'currency' => 'EUR']);

        $this->assertNull(
            app(ProjectTimeStatsService::class)->billingRateForProject($user, $project->id),
        );
    }

    public function test_non_member_cannot_read_billing_rate(): void
    {
        [, $project] = $this->bootScenario();
        $project->team->update(['hourly_rate' => '50.00', 'currency' => 'CHF']);
        $stranger = User::factory()->create();

        $this->expectException(AuthorizationException::class);
        app(ProjectTimeStatsService::class)->billingRateForProject($stranger, $project->id);
    }
}
