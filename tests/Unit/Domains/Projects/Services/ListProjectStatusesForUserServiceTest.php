<?php

namespace Tests\Unit\Domains\Projects\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Projects\Services\ListProjectStatusesForUserService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListProjectStatusesForUserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_delegates_to_action_for_the_actor(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);

        $result = app(ListProjectStatusesForUserService::class)->execute($user);

        $this->assertSame([$status->id], $result->pluck('id')->all());
    }

    public function test_returns_empty_for_user_with_no_teams(): void
    {
        $user = User::factory()->create();

        $result = app(ListProjectStatusesForUserService::class)->execute($user);

        $this->assertTrue($result->isEmpty());
    }
}
