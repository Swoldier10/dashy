<?php

namespace Tests\Unit\Domains\Projects\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Projects\Services\ListProjectStatusesForProjectService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListProjectStatusesForProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_lists_statuses(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $s1 = ProjectStatus::factory()->create(['project_id' => $project->id]);
        $s2 = ProjectStatus::factory()->create(['project_id' => $project->id]);

        $statuses = app(ListProjectStatusesForProjectService::class)->execute($user, $project);

        $ids = $statuses->pluck('id')->all();
        sort($ids);
        $expected = [$s1->id, $s2->id];
        sort($expected);
        $this->assertSame($expected, $ids);
    }

    public function test_stranger_cannot_list(): void
    {
        $stranger = User::factory()->create();
        $project = Project::factory()->create();

        $this->expectException(AuthorizationException::class);

        app(ListProjectStatusesForProjectService::class)->execute($stranger, $project);
    }
}
