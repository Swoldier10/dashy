<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\UpdateTaskService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateTaskServiceTest extends TestCase
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

    public function test_updates_name_and_description(): void
    {
        [$user, $task] = $this->memberTask();

        $updated = app(UpdateTaskService::class)->execute($user, $task->id, [
            'name' => 'Renamed',
            'description' => 'Body',
        ]);

        $this->assertSame('Renamed', $updated->name);
        $this->assertSame('Body', $updated->description);
    }

    public function test_non_member_cannot_update(): void
    {
        $task = Task::factory()->create();
        $stranger = User::factory()->create();

        $this->expectException(AuthorizationException::class);

        app(UpdateTaskService::class)->execute($stranger, $task->id, ['name' => 'Hijack']);
    }

    public function test_validates_end_date_after_start(): void
    {
        [$user, $task] = $this->memberTask();

        $this->expectException(ValidationException::class);

        app(UpdateTaskService::class)->execute($user, $task->id, [
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-09',
        ]);
    }
}
