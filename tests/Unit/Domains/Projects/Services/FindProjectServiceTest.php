<?php

namespace Tests\Unit\Domains\Projects\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Services\FindProjectService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_member_can_find(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        $found = app(FindProjectService::class)->execute($user, $project->id);

        $this->assertTrue($project->is($found));
    }

    public function test_stranger_cannot_find(): void
    {
        $stranger = User::factory()->create();
        $project = Project::factory()->create();

        $this->expectException(AuthorizationException::class);

        app(FindProjectService::class)->execute($stranger, $project->id);
    }
}
