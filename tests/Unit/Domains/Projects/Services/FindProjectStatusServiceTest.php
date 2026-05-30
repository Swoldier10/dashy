<?php

namespace Tests\Unit\Domains\Projects\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Projects\Services\FindProjectStatusService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindProjectStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_find_status(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);

        $found = app(FindProjectStatusService::class)->execute($user, $status->id);

        $this->assertTrue($status->is($found));
    }

    public function test_stranger_cannot_find(): void
    {
        $stranger = User::factory()->create();
        $project = Project::factory()->create();
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);

        $this->expectException(AuthorizationException::class);

        app(FindProjectStatusService::class)->execute($stranger, $status->id);
    }
}
