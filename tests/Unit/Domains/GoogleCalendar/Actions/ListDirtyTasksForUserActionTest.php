<?php

namespace Tests\Unit\Domains\GoogleCalendar\Actions;

use App\Domains\GoogleCalendar\Actions\ListDirtyTasksForUserAction;
use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListDirtyTasksForUserActionTest extends TestCase
{
    use RefreshDatabase;

    private Project $project;

    private ProjectStatus $status;

    protected function setUp(): void
    {
        parent::setUp();

        $this->project = Project::factory()->create();
        $this->status = ProjectStatus::factory()->create(['project_id' => $this->project->id, 'position' => 0]);
    }

    public function test_returns_tasks_where_user_is_assignee(): void
    {
        $user = User::factory()->create();
        $connection = GoogleCalendarConnection::factory()->for($user)->create();

        $task = Task::factory()->forProject($this->project, $this->status)->create([
            'start_date' => CarbonImmutable::now()->addDays(1),
        ]);
        $task->assignees()->attach($user->id, ['assigned_by_user_id' => $user->id]);

        $result = (new ListDirtyTasksForUserAction)->execute($connection);

        $this->assertCount(1, $result);
        $this->assertSame($task->id, $result->first()->id);
    }

    public function test_returns_tasks_for_creator_when_no_assignees(): void
    {
        $user = User::factory()->create();
        $connection = GoogleCalendarConnection::factory()->for($user)->create();

        $task = Task::factory()->forProject($this->project, $this->status)->create([
            'created_by_user_id' => $user->id,
            'start_date' => CarbonImmutable::now()->addDays(1),
        ]);

        $result = (new ListDirtyTasksForUserAction)->execute($connection);

        $this->assertCount(1, $result);
        $this->assertSame($task->id, $result->first()->id);
    }

    public function test_skips_tasks_where_user_is_creator_but_other_assignees_exist(): void
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $connection = GoogleCalendarConnection::factory()->for($creator)->create();

        $task = Task::factory()->forProject($this->project, $this->status)->create([
            'created_by_user_id' => $creator->id,
            'start_date' => CarbonImmutable::now()->addDays(1),
        ]);
        $task->assignees()->attach($assignee->id, ['assigned_by_user_id' => $creator->id]);

        $this->assertCount(0, (new ListDirtyTasksForUserAction)->execute($connection));
    }

    public function test_skips_tasks_without_start_date(): void
    {
        $user = User::factory()->create();
        $connection = GoogleCalendarConnection::factory()->for($user)->create();

        Task::factory()->forProject($this->project, $this->status)->create([
            'created_by_user_id' => $user->id,
            'start_date' => null,
        ]);

        $this->assertCount(0, (new ListDirtyTasksForUserAction)->execute($connection));
    }

    public function test_skips_archived_tasks(): void
    {
        $user = User::factory()->create();
        $connection = GoogleCalendarConnection::factory()->for($user)->create();

        Task::factory()->forProject($this->project, $this->status)->archived()->create([
            'created_by_user_id' => $user->id,
            'start_date' => CarbonImmutable::now()->addDays(1),
        ]);

        $this->assertCount(0, (new ListDirtyTasksForUserAction)->execute($connection));
    }

    public function test_skips_past_tasks(): void
    {
        $user = User::factory()->create();
        $connection = GoogleCalendarConnection::factory()->for($user)->create();

        Task::factory()->forProject($this->project, $this->status)->create([
            'created_by_user_id' => $user->id,
            'start_date' => CarbonImmutable::now()->subDays(2),
        ]);

        $this->assertCount(0, (new ListDirtyTasksForUserAction)->execute($connection));
    }
}
