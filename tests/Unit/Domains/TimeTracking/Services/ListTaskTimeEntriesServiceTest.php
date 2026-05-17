<?php

namespace Tests\Unit\Domains\TimeTracking\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Domains\TimeTracking\Services\ListTaskTimeEntriesService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListTaskTimeEntriesServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_entries_and_total(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->forProject($project, $status)->create();

        TimeEntry::factory()->forTask($task)->forUser($user)->create(['duration_seconds' => 600]);
        TimeEntry::factory()->forTask($task)->forUser($user)->create(['duration_seconds' => 1200]);

        $result = app(ListTaskTimeEntriesService::class)->execute($user, $task);

        $this->assertCount(2, $result['entries']);
        $this->assertSame(1800, $result['total_seconds']);
    }

    public function test_non_member_cannot_list(): void
    {
        $task = Task::factory()->create();
        $stranger = User::factory()->create();

        $this->expectException(AuthorizationException::class);
        app(ListTaskTimeEntriesService::class)->execute($stranger, $task);
    }
}
