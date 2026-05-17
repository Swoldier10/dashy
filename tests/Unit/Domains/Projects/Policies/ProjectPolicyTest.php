<?php

namespace Tests\Unit\Domains\Projects\Policies;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Policies\ProjectPolicy;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_member_can_create(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);

        $this->assertTrue((new ProjectPolicy)->create($user, $team));
    }

    public function test_team_owner_can_create(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Owner->value]);

        $this->assertTrue((new ProjectPolicy)->create($user, $team));
    }

    public function test_non_member_cannot_create(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->assertFalse((new ProjectPolicy)->create($user, $team));
    }

    public function test_owner_can_delete(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        $this->assertTrue((new ProjectPolicy)->delete($user, $project));
    }

    public function test_member_cannot_delete(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        $this->assertFalse((new ProjectPolicy)->delete($user, $project));
    }

    public function test_non_member_cannot_delete(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $project = Project::factory()->create(['team_id' => $team->id]);

        $this->assertFalse((new ProjectPolicy)->delete($user, $project));
    }

    public function test_owner_can_update(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        $this->assertTrue((new ProjectPolicy)->update($user, $project));
    }

    public function test_member_cannot_update(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        $this->assertFalse((new ProjectPolicy)->update($user, $project));
    }

    public function test_non_member_cannot_update(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $project = Project::factory()->create(['team_id' => $team->id]);

        $this->assertFalse((new ProjectPolicy)->update($user, $project));
    }

    public function test_owner_can_manage_statuses(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        $this->assertTrue((new ProjectPolicy)->manageStatuses($user, $project));
    }

    public function test_member_cannot_manage_statuses(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);

        $this->assertFalse((new ProjectPolicy)->manageStatuses($user, $project));
    }
}
