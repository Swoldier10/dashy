<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\BulkUpdateTaskDueDateService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BulkUpdateTaskDueDateServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): BulkUpdateTaskDueDateService
    {
        return app(BulkUpdateTaskDueDateService::class);
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

    public function test_sets_due_date_on_a_batch_of_tasks(): void
    {
        [$actor, $project] = $this->actorWithProject();
        $a = Task::factory()->create(['project_id' => $project->id]);
        $b = Task::factory()->create(['project_id' => $project->id]);

        $updated = $this->service()->execute($actor, [$a->id, $b->id], '2026-06-10');

        $this->assertCount(2, $updated);
        $this->assertSame('2026-06-10', $a->refresh()->end_date?->toDateString());
        $this->assertSame('2026-06-10', $b->refresh()->end_date?->toDateString());
        $this->assertNull($a->start_date);
    }

    public function test_preserves_a_non_conflicting_start_date(): void
    {
        [$actor, $project] = $this->actorWithProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-03',
        ]);

        $this->service()->execute($actor, [$task->id], '2026-06-10');

        $task->refresh();
        $this->assertSame('2026-06-01', $task->start_date?->toDateString());
        $this->assertSame('2026-06-10', $task->end_date?->toDateString());
    }

    public function test_clears_a_conflicting_start_date_so_the_batch_succeeds(): void
    {
        [$actor, $project] = $this->actorWithProject();
        $conflicting = Task::factory()->create([
            'project_id' => $project->id,
            'start_date' => '2026-06-20',
            'end_date' => '2026-06-25',
        ]);
        $clean = Task::factory()->create(['project_id' => $project->id]);

        $updated = $this->service()->execute($actor, [$conflicting->id, $clean->id], '2026-06-10');

        $this->assertCount(2, $updated);
        $conflicting->refresh();
        $this->assertNull($conflicting->start_date, 'A start date after the new due date is cleared.');
        $this->assertSame('2026-06-10', $conflicting->end_date?->toDateString());
        $this->assertSame('2026-06-10', $clean->refresh()->end_date?->toDateString());
    }

    public function test_null_clears_both_start_and_end_dates(): void
    {
        [$actor, $project] = $this->actorWithProject();
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-03',
        ]);

        $updated = $this->service()->execute($actor, [$task->id], null);

        $this->assertCount(1, $updated);
        $task->refresh();
        $this->assertNull($task->start_date);
        $this->assertNull($task->end_date);
    }

    public function test_rejects_an_invalid_date(): void
    {
        [$actor, $project] = $this->actorWithProject();
        $task = Task::factory()->create(['project_id' => $project->id, 'end_date' => '2026-06-03']);

        try {
            $this->service()->execute($actor, [$task->id], 'not-a-date');
            $this->fail('Expected ValidationException.');
        } catch (ValidationException) {
            // expected
        }

        $this->assertSame('2026-06-03', $task->refresh()->end_date?->toDateString());
    }

    public function test_rolls_back_when_actor_is_unauthorized_on_a_task(): void
    {
        [$actor, $project] = $this->actorWithProject();
        $ok = Task::factory()->create(['project_id' => $project->id]);
        $forbidden = Task::factory()->create(['project_id' => Project::factory()->create()->id]);

        try {
            $this->service()->execute($actor, [$ok->id, $forbidden->id], '2026-06-10');
            $this->fail('Expected AuthorizationException.');
        } catch (AuthorizationException) {
            // expected
        }

        $this->assertNull($ok->refresh()->end_date, 'No task may change dates if any fails the gate.');
    }

    public function test_rejects_an_empty_task_id_list(): void
    {
        [$actor] = $this->actorWithProject();

        $this->expectException(ValidationException::class);

        $this->service()->execute($actor, [], '2026-06-10');
    }
}
