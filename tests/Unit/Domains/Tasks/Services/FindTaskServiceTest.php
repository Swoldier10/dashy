<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\FindTaskService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindTaskServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_find(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);

        $found = app(FindTaskService::class)->execute($user, $task->id);

        $this->assertSame($task->id, $found->id);
    }

    public function test_stranger_cannot_find(): void
    {
        $stranger = User::factory()->create();
        $task = Task::factory()->create();

        $this->expectException(AuthorizationException::class);

        app(FindTaskService::class)->execute($stranger, $task->id);
    }
}
