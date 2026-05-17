<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\ListTasksForUserInRangeService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListTasksForUserInRangeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_member_tasks_in_window(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-15',
        ]);

        $result = app(ListTasksForUserInRangeService::class)->execute(
            $user,
            CarbonImmutable::parse('2026-06-15 00:00:00'),
            CarbonImmutable::parse('2026-06-15 23:59:59'),
        );

        $this->assertCount(1, $result);
        $this->assertSame($task->id, $result->first()->id);
    }

    public function test_filters_to_assignee_when_only_mine(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Member->value]);
        $team->members()->attach($other->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        $mine = Task::factory()->create([
            'project_id' => $project->id,
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-15',
        ]);
        $mine->assignees()->attach($owner->id);

        $theirs = Task::factory()->create([
            'project_id' => $project->id,
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-15',
        ]);
        $theirs->assignees()->attach($other->id);

        $result = app(ListTasksForUserInRangeService::class)->execute(
            $owner,
            CarbonImmutable::parse('2026-06-15 00:00:00'),
            CarbonImmutable::parse('2026-06-15 23:59:59'),
            onlyMine: true,
        );

        $this->assertCount(1, $result);
        $this->assertSame($mine->id, $result->first()->id);
    }
}
