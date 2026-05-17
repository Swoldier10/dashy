<?php

namespace Tests\Unit\Domains\Projects\Actions;

use App\Domains\Projects\Actions\FindProjectWithTeamMembersAction;
use App\Domains\Projects\Models\Project;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindProjectWithTeamMembersActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_project_with_team_members_eager_loaded(): void
    {
        $owner = User::factory()->create(['name' => 'Zoe']);
        $member = User::factory()->create(['name' => 'Adam']);
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        $found = (new FindProjectWithTeamMembersAction)->execute($project->id);

        $this->assertTrue($project->is($found));
        $this->assertTrue($found->relationLoaded('team'));
        $this->assertTrue($found->team->relationLoaded('members'));
        $this->assertSame(
            ['Adam', 'Zoe'],
            $found->team->members->pluck('name')->sort()->values()->all(),
        );
    }

    public function test_throws_when_project_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        (new FindProjectWithTeamMembersAction)->execute(99999);
    }
}
