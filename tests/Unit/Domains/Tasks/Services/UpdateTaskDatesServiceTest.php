<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\UpdateTaskDatesService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateTaskDatesServiceTest extends TestCase
{
    use RefreshDatabase;

    private function memberTask(): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);

        return [$user, $task];
    }

    public function test_sets_dates(): void
    {
        [$user, $task] = $this->memberTask();

        $updated = app(UpdateTaskDatesService::class)->execute($user, $task->id, '2026-05-10', '2026-05-15');

        $this->assertSame('2026-05-10', $updated->start_date->toDateString());
        $this->assertSame('2026-05-15', $updated->end_date->toDateString());
    }

    public function test_clears_dates(): void
    {
        [$user, $task] = $this->memberTask();
        $task->forceFill(['start_date' => '2026-05-10', 'end_date' => '2026-05-15'])->save();

        $updated = app(UpdateTaskDatesService::class)->execute($user, $task->id, null, null);

        $this->assertNull($updated->start_date);
        $this->assertNull($updated->end_date);
    }

    public function test_rejects_end_before_start(): void
    {
        [$user, $task] = $this->memberTask();

        $this->expectException(ValidationException::class);

        app(UpdateTaskDatesService::class)->execute($user, $task->id, '2026-05-10', '2026-05-09');
    }
}
