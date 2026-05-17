<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\DeleteTaskService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTaskServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_delete(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);

        app(DeleteTaskService::class)->execute($user, $task->id);

        $this->assertSame(0, Task::count());
    }

    public function test_non_member_cannot_delete(): void
    {
        $stranger = User::factory()->create();
        $task = Task::factory()->create();

        $this->expectException(AuthorizationException::class);

        app(DeleteTaskService::class)->execute($stranger, $task->id);
    }
}
