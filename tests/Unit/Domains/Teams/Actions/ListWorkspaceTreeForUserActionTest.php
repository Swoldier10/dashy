<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Projects\Models\Project;
use App\Domains\Teams\Actions\ListWorkspaceTreeForUserAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListWorkspaceTreeForUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_the_users_teams_with_projects_eager_loaded(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        Project::factory()->create(['team_id' => $team->id]);

        $teams = (new ListWorkspaceTreeForUserAction)->execute($user);

        $this->assertCount(1, $teams);
        $this->assertTrue($teams->first()->relationLoaded('members'));
        $this->assertTrue($teams->first()->relationLoaded('projects'));
        $this->assertCount(1, $teams->first()->projects);
    }

    public function test_excludes_teams_the_user_does_not_belong_to(): void
    {
        $user = User::factory()->create();
        $mine = Team::factory()->create();
        $mine->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        Team::factory()->create();

        $teams = (new ListWorkspaceTreeForUserAction)->execute($user);

        $this->assertSame([$mine->id], $teams->pluck('id')->all());
    }
}
