<?php

namespace Tests\Unit\Domains\Tasks\Actions;

use App\Domains\Projects\Enums\ProjectStatusCategory;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Actions\ListTasksByDueStateAction;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListTasksByDueStateActionTest extends TestCase
{
    use RefreshDatabase;

    private CarbonImmutable $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = CarbonImmutable::parse('2026-06-02 12:00:00');
    }

    private function makeTask(ProjectStatusCategory $category, ?string $endDate, bool $archived = false): Task
    {
        $project = Project::factory()->create();
        $status = ProjectStatus::factory()->create([
            'project_id' => $project->id,
            'category' => $category->value,
        ]);

        return Task::factory()->create([
            'project_id' => $project->id,
            'project_status_id' => $status->id,
            'created_by_user_id' => User::factory()->create()->id,
            'end_date' => $endDate,
            'is_archived' => $archived,
        ]);
    }

    public function test_due_soon_returns_only_tasks_inside_the_window_across_users(): void
    {
        $inWindow = $this->makeTask(ProjectStatusCategory::Active, '2026-06-02 18:00:00');
        $otherTeamInWindow = $this->makeTask(ProjectStatusCategory::NotStarted, '2026-06-03 09:00:00');
        $this->makeTask(ProjectStatusCategory::Active, '2026-06-05 12:00:00'); // beyond 24h
        $this->makeTask(ProjectStatusCategory::Active, null); // no due date

        $result = (new ListTasksByDueStateAction)->execute('due_soon', $this->now, $this->now->addDay());

        $this->assertSame(
            [$inWindow->id, $otherTeamInWindow->id],
            $result->pluck('id')->all(),
        );
    }

    public function test_overdue_returns_tasks_past_due_excluding_done_closed_and_archived(): void
    {
        $overdue = $this->makeTask(ProjectStatusCategory::Active, '2026-06-01 09:00:00');
        $this->makeTask(ProjectStatusCategory::Done, '2026-06-01 09:00:00');
        $this->makeTask(ProjectStatusCategory::Closed, '2026-06-01 09:00:00');
        $this->makeTask(ProjectStatusCategory::Active, '2026-06-01 09:00:00', archived: true);
        $this->makeTask(ProjectStatusCategory::Active, '2026-06-04 09:00:00'); // not yet due

        $result = (new ListTasksByDueStateAction)->execute('overdue', $this->now, $this->now);

        $this->assertSame([$overdue->id], $result->pluck('id')->all());
    }

    public function test_eager_loads_assignees_and_project(): void
    {
        $task = $this->makeTask(ProjectStatusCategory::Active, '2026-06-02 18:00:00');

        $result = (new ListTasksByDueStateAction)->execute('due_soon', $this->now, $this->now->addDay());

        $this->assertTrue($result->first()->relationLoaded('assignees'));
        $this->assertTrue($result->first()->relationLoaded('project'));
        $this->assertSame($task->id, $result->first()->id);
    }
}
