<?php

namespace Tests\Unit\Domains\Tasks\Actions;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Actions\ListTasksForUserAction;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListTasksForUserActionTest extends TestCase
{
    use RefreshDatabase;

    private function memberProject(): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        return [$user, $project];
    }

    public function test_range_filter_includes_tasks_overlapping_window(): void
    {
        [$user, $project] = $this->memberProject();

        $inRange = Task::factory()->create([
            'project_id' => $project->id,
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-12',
        ]);

        $earlier = Task::factory()->create([
            'project_id' => $project->id,
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-05',
        ]);

        $later = Task::factory()->create([
            'project_id' => $project->id,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-05',
        ]);

        $unscheduled = Task::factory()->create([
            'project_id' => $project->id,
            'start_date' => null,
            'end_date' => null,
        ]);

        $result = (new ListTasksForUserAction)->execute($user, [
            'range_from' => '2026-06-01',
            'range_to' => '2026-06-30',
        ]);

        $ids = $result->pluck('id')->all();

        $this->assertContains($inRange->id, $ids);
        $this->assertNotContains($earlier->id, $ids);
        $this->assertNotContains($later->id, $ids);
        $this->assertNotContains($unscheduled->id, $ids);
    }

    public function test_task_starting_in_range_with_null_end_date_is_included(): void
    {
        [$user, $project] = $this->memberProject();

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'start_date' => '2026-06-10',
            'end_date' => null,
        ]);

        $result = (new ListTasksForUserAction)->execute($user, [
            'range_from' => '2026-06-01',
            'range_to' => '2026-06-30',
        ]);

        $this->assertTrue($result->contains('id', $task->id));
    }

    public function test_range_filter_excludes_tasks_for_non_member_projects(): void
    {
        $stranger = User::factory()->create();
        [$user, $project] = $this->memberProject();

        Task::factory()->create([
            'project_id' => $project->id,
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-12',
        ]);

        $result = (new ListTasksForUserAction)->execute($stranger, [
            'range_from' => '2026-06-01',
            'range_to' => '2026-06-30',
        ]);

        $this->assertCount(0, $result);
    }
}
