<?php

namespace Tests\Unit\Domains\TimeTracking\Policies;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Domains\TimeTracking\Policies\TimeEntryPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeEntryPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_member_can_view_and_create_for_a_task(): void
    {
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->forProject($project, $status)->create();

        $policy = new TimeEntryPolicy;
        $this->assertTrue($policy->viewAny($member, $task));
        $this->assertTrue($policy->create($member, $task));
    }

    public function test_stranger_cannot_view_or_create(): void
    {
        $task = Task::factory()->create();
        $stranger = User::factory()->create();

        $policy = new TimeEntryPolicy;
        $this->assertFalse($policy->viewAny($stranger, $task));
        $this->assertFalse($policy->create($stranger, $task));
    }

    public function test_owner_can_update_and_delete_own_entry(): void
    {
        $user = User::factory()->create();
        $entry = TimeEntry::factory()->forUser($user)->create();

        $policy = new TimeEntryPolicy;
        $this->assertTrue($policy->update($user, $entry));
        $this->assertTrue($policy->delete($user, $entry));
    }

    public function test_team_member_can_update_or_delete_others_entry(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->forProject($project, $status)->create();
        $entry = TimeEntry::factory()->forUser($owner)->forTask($task)->create();

        $teammate = User::factory()->create();
        $team->members()->attach([
            $owner->id => ['role' => TeamRole::Member->value],
            $teammate->id => ['role' => TeamRole::Member->value],
        ]);

        $policy = new TimeEntryPolicy;
        $this->assertTrue($policy->update($teammate, $entry));
        $this->assertTrue($policy->delete($teammate, $entry));
    }

    public function test_stranger_cannot_update_or_delete(): void
    {
        $owner = User::factory()->create();
        $entry = TimeEntry::factory()->forUser($owner)->create();
        $stranger = User::factory()->create();

        $policy = new TimeEntryPolicy;
        $this->assertFalse($policy->update($stranger, $entry));
        $this->assertFalse($policy->delete($stranger, $entry));
    }
}
