<?php

namespace Tests\Unit\Domains\Projects\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Services\ListProjectsForUserService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListProjectsForUserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_users_projects_only(): void
    {
        $user = User::factory()->create();
        $myTeam = Team::factory()->create();
        $myTeam->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $otherTeam = Team::factory()->create();

        $mine = Project::factory()->create(['team_id' => $myTeam->id, 'name' => 'Mine']);
        Project::factory()->create(['team_id' => $otherTeam->id, 'name' => 'Theirs']);

        $projects = app(ListProjectsForUserService::class)->execute($user);

        $this->assertCount(1, $projects);
        $this->assertSame($mine->id, $projects->first()->id);
    }

    public function test_returns_empty_when_user_has_no_teams(): void
    {
        $user = User::factory()->create();

        $projects = app(ListProjectsForUserService::class)->execute($user);

        $this->assertCount(0, $projects);
    }
}
