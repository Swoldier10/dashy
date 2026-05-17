<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\MoveTaskService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MoveTaskServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_moves_across_status_and_reorders_both_groups(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $statusA = ProjectStatus::factory()->create(['project_id' => $project->id]);
        $statusB = ProjectStatus::factory()->create(['project_id' => $project->id]);

        $a1 = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $statusA->id, 'position' => 0]);
        $a2 = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $statusA->id, 'position' => 1]);
        $b1 = Task::factory()->create(['project_id' => $project->id, 'project_status_id' => $statusB->id, 'position' => 0]);

        // Move $a1 → statusB at index 1.
        // Source remaining: [$a2]. Target after insert: [$b1, $a1].
        app(MoveTaskService::class)->execute(
            $user,
            $a1->id,
            $statusB->id,
            [$a2->id],
            [$b1->id, $a1->id],
        );

        $a1->refresh();
        $this->assertSame($statusB->id, $a1->project_status_id);
        $this->assertSame(1, $a1->position);

        $this->assertSame(0, $a2->refresh()->position);
        $this->assertSame(0, $b1->refresh()->position);
    }

    public function test_rejects_target_status_from_other_project(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);
        $otherStatus = ProjectStatus::factory()->create();

        $this->expectException(ValidationException::class);

        app(MoveTaskService::class)->execute($user, $task->id, $otherStatus->id, [], [$task->id]);
    }
}
