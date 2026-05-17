<?php

namespace Tests\Unit\Domains\Tasks\Policies;

use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Models\Task;
use App\Domains\Tasks\Policies\TaskPolicy;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_act(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);

        $policy = new TaskPolicy;

        $this->assertTrue($policy->viewAny($user, $project));
        $this->assertTrue($policy->view($user, $task));
        $this->assertTrue($policy->create($user, $project));
        $this->assertTrue($policy->update($user, $task));
        $this->assertTrue($policy->delete($user, $task));
    }

    public function test_non_member_cannot_act(): void
    {
        $stranger = User::factory()->create();
        $project = Project::factory()->create();
        $task = Task::factory()->create(['project_id' => $project->id]);

        $policy = new TaskPolicy;

        $this->assertFalse($policy->viewAny($stranger, $project));
        $this->assertFalse($policy->view($stranger, $task));
        $this->assertFalse($policy->create($stranger, $project));
        $this->assertFalse($policy->update($stranger, $task));
        $this->assertFalse($policy->delete($stranger, $task));
    }
}
