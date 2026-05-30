<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\BulkDeleteTasksService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BulkDeleteTasksServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): BulkDeleteTasksService
    {
        return app(BulkDeleteTasksService::class);
    }

    public function test_deletes_a_batch_and_returns_the_count(): void
    {
        $actor = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($actor->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $a = Task::factory()->create(['project_id' => $project->id]);
        $b = Task::factory()->create(['project_id' => $project->id]);

        $count = $this->service()->execute($actor, [$a->id, $b->id]);

        $this->assertSame(2, $count);
        $this->assertDatabaseMissing('tasks', ['id' => $a->id]);
        $this->assertDatabaseMissing('tasks', ['id' => $b->id]);
    }

    public function test_rolls_back_the_whole_batch_when_one_task_is_unauthorized(): void
    {
        $actor = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($actor->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $ok = Task::factory()->create(['project_id' => $project->id]);
        $forbidden = Task::factory()->create(['project_id' => Project::factory()->create()->id]);

        try {
            $this->service()->execute($actor, [$ok->id, $forbidden->id]);
            $this->fail('Expected AuthorizationException.');
        } catch (AuthorizationException) {
            // expected
        }

        // No task may be deleted if any fails the gate (atomic rollback).
        $this->assertDatabaseHas('tasks', ['id' => $ok->id]);
    }

    public function test_rejects_an_empty_task_id_list(): void
    {
        $this->expectException(ValidationException::class);

        $this->service()->execute(User::factory()->create(), []);
    }
}
