<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\BulkArchiveTasksService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BulkArchiveTasksServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): BulkArchiveTasksService
    {
        return app(BulkArchiveTasksService::class);
    }

    /**
     * @return array{0: User, 1: Project}
     */
    private function actorWithProject(): array
    {
        $actor = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($actor->id, ['role' => TeamRole::Member->value]);

        return [$actor, Project::factory()->create(['team_id' => $team->id])];
    }

    public function test_archives_a_batch_of_tasks(): void
    {
        [$actor, $project] = $this->actorWithProject();
        $a = Task::factory()->create(['project_id' => $project->id, 'is_archived' => false]);
        $b = Task::factory()->create(['project_id' => $project->id, 'is_archived' => false]);

        $archived = $this->service()->execute($actor, [$a->id, $b->id]);

        $this->assertCount(2, $archived);
        $this->assertTrue($a->refresh()->is_archived);
        $this->assertTrue($b->refresh()->is_archived);
    }

    public function test_already_archived_tasks_pass_through_unchanged(): void
    {
        [$actor, $project] = $this->actorWithProject();
        $task = Task::factory()->create(['project_id' => $project->id, 'is_archived' => true]);

        $archived = $this->service()->execute($actor, [$task->id]);

        $this->assertCount(1, $archived);
        $this->assertTrue($task->refresh()->is_archived);
    }

    public function test_rolls_back_when_actor_is_unauthorized_on_a_task(): void
    {
        [$actor, $project] = $this->actorWithProject();
        $ok = Task::factory()->create(['project_id' => $project->id, 'is_archived' => false]);
        $forbidden = Task::factory()->create(['project_id' => Project::factory()->create()->id, 'is_archived' => false]);

        try {
            $this->service()->execute($actor, [$ok->id, $forbidden->id]);
            $this->fail('Expected AuthorizationException.');
        } catch (AuthorizationException) {
            // expected
        }

        $this->assertFalse($ok->refresh()->is_archived, 'No task may be archived if any fails the gate.');
    }

    public function test_rejects_an_empty_task_id_list(): void
    {
        [$actor] = $this->actorWithProject();

        $this->expectException(ValidationException::class);

        $this->service()->execute($actor, []);
    }
}
