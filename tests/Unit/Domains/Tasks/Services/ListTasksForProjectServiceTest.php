<?php

namespace Tests\Unit\Domains\Tasks\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Services\ListTasksForProjectService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListTasksForProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_list(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        Task::factory()->count(3)->create(['project_id' => $project->id]);

        $list = app(ListTasksForProjectService::class)->execute($user, $project);

        $this->assertCount(3, $list);
    }

    public function test_non_member_cannot_list(): void
    {
        $stranger = User::factory()->create();
        $project = Project::factory()->create();

        $this->expectException(AuthorizationException::class);

        app(ListTasksForProjectService::class)->execute($stranger, $project);
    }
}
