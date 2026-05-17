<?php

namespace Tests\Unit\Domains\Calendar\Services;

use App\Domains\Calendar\Services\ListScheduledTasksForUserInRangeService;
use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListScheduledTasksForUserInRangeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_overlays_member_tasks_for_calendar(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        Task::factory()->create([
            'project_id' => $project->id,
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-10',
        ]);

        $result = app(ListScheduledTasksForUserInRangeService::class)->execute(
            $user,
            CarbonImmutable::parse('2026-06-08 00:00:00'),
            CarbonImmutable::parse('2026-06-14 23:59:59'),
        );

        $this->assertCount(1, $result);
    }

    public function test_excludes_tasks_from_non_member_projects(): void
    {
        $stranger = User::factory()->create();
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        Task::factory()->create([
            'project_id' => $project->id,
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-10',
        ]);

        $result = app(ListScheduledTasksForUserInRangeService::class)->execute(
            $stranger,
            CarbonImmutable::parse('2026-06-08 00:00:00'),
            CarbonImmutable::parse('2026-06-14 23:59:59'),
        );

        $this->assertCount(0, $result);
    }
}
