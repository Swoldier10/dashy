<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Enums\TaskPriority;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\BulkUpdateTaskPriorityService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BulkUpdateTaskPriorityServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): BulkUpdateTaskPriorityService
    {
        return app(BulkUpdateTaskPriorityService::class);
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

    public function test_sets_priority_on_a_batch_of_tasks(): void
    {
        [$actor, $project] = $this->actorWithProject();
        $a = Task::factory()->create(['project_id' => $project->id, 'priority' => TaskPriority::Low->value]);
        $b = Task::factory()->create(['project_id' => $project->id, 'priority' => TaskPriority::Normal->value]);

        $updated = $this->service()->execute($actor, [$a->id, $b->id], TaskPriority::Urgent->value);

        $this->assertCount(2, $updated);
        $this->assertSame(TaskPriority::Urgent, $a->refresh()->priority);
        $this->assertSame(TaskPriority::Urgent, $b->refresh()->priority);
    }

    public function test_is_idempotent_when_a_task_already_has_the_priority(): void
    {
        [$actor, $project] = $this->actorWithProject();
        $task = Task::factory()->create(['project_id' => $project->id, 'priority' => TaskPriority::High->value]);

        $updated = $this->service()->execute($actor, [$task->id], TaskPriority::High->value);

        $this->assertCount(1, $updated);
        $this->assertSame(TaskPriority::High, $task->refresh()->priority);
    }

    public function test_rejects_an_invalid_priority(): void
    {
        [$actor, $project] = $this->actorWithProject();
        $task = Task::factory()->create(['project_id' => $project->id, 'priority' => TaskPriority::Low->value]);

        try {
            $this->service()->execute($actor, [$task->id], 'bogus');
            $this->fail('Expected ValidationException.');
        } catch (ValidationException) {
            // expected
        }

        $this->assertSame(TaskPriority::Low, $task->refresh()->priority);
    }

    public function test_rolls_back_when_actor_is_unauthorized_on_a_task(): void
    {
        [$actor, $project] = $this->actorWithProject();
        $ok = Task::factory()->create(['project_id' => $project->id, 'priority' => TaskPriority::Low->value]);
        $forbidden = Task::factory()->create(['project_id' => Project::factory()->create()->id, 'priority' => TaskPriority::Low->value]);

        try {
            $this->service()->execute($actor, [$ok->id, $forbidden->id], TaskPriority::Urgent->value);
            $this->fail('Expected AuthorizationException.');
        } catch (AuthorizationException) {
            // expected
        }

        $this->assertSame(TaskPriority::Low, $ok->refresh()->priority, 'No task may change priority if any fails the gate.');
    }

    public function test_rejects_an_empty_task_id_list(): void
    {
        [$actor] = $this->actorWithProject();

        $this->expectException(ValidationException::class);

        $this->service()->execute($actor, [], TaskPriority::Urgent->value);
    }
}
