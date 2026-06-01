<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\RemoveTeamMemberService;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RemoveTeamMemberServiceTest extends TestCase
{
    use RefreshDatabase;

    private function taskInTeam(Team $team): Task
    {
        $project = Project::factory()->create(['team_id' => $team->id]);
        $status = ProjectStatus::factory()->create(['project_id' => $project->id]);

        return Task::factory()->forProject($project, $status)->create();
    }

    public function test_owner_can_remove_member(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);

        app(RemoveTeamMemberService::class)->execute($owner, $team, $member);

        $this->assertDatabaseMissing('team_user', [
            'team_id' => $team->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_member_can_self_leave(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);

        app(RemoveTeamMemberService::class)->execute($member, $team, $member);

        $this->assertDatabaseMissing('team_user', [
            'team_id' => $team->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_last_owner_cannot_be_removed(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);

        $this->expectException(ValidationException::class);

        try {
            app(RemoveTeamMemberService::class)->execute($owner, $team, $owner);
        } finally {
            $this->assertDatabaseHas('team_user', [
                'team_id' => $team->id,
                'user_id' => $owner->id,
            ]);
        }
    }

    public function test_member_cannot_leave_personal_team(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->personal()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);

        $this->expectException(ValidationException::class);

        app(RemoveTeamMemberService::class)->execute($owner, $team, $owner);
    }

    public function test_non_owner_cannot_remove_other_member(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $other = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);
        $team->members()->attach($other->id, ['role' => TeamRole::Member->value]);

        $this->expectException(AuthorizationException::class);

        app(RemoveTeamMemberService::class)->execute($member, $team, $other);
    }

    public function test_one_of_two_owners_can_be_removed(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($a->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($b->id, ['role' => TeamRole::Owner->value]);

        app(RemoveTeamMemberService::class)->execute($a, $team, $b);

        $this->assertDatabaseMissing('team_user', [
            'team_id' => $team->id,
            'user_id' => $b->id,
        ]);
    }

    public function test_removing_member_clears_their_assignments_but_keeps_time_entries(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);

        $task = $this->taskInTeam($team);
        $task->assignees()->attach([$member->id, $owner->id]);

        $completed = TimeEntry::factory()->forTask($task)->forUser($member)->create();
        $running = TimeEntry::factory()->forTask($task)->forUser($member)->running()->create();

        app(RemoveTeamMemberService::class)->execute($owner, $team, $member);

        // Assignment cleared for the removed member, untouched for the owner.
        $this->assertDatabaseMissing('task_user', ['task_id' => $task->id, 'user_id' => $member->id]);
        $this->assertDatabaseHas('task_user', ['task_id' => $task->id, 'user_id' => $owner->id]);

        // Time entries are RETAINED — including the still-running one (unchanged).
        $this->assertDatabaseHas('time_entries', ['id' => $completed->id, 'user_id' => $member->id]);
        $this->assertDatabaseHas('time_entries', ['id' => $running->id, 'user_id' => $member->id, 'ended_at' => null]);
        $this->assertSame(2, TimeEntry::where('user_id', $member->id)->count());
    }

    public function test_removing_member_only_clears_assignments_in_that_team(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $teamA = Team::factory()->create();
        $teamA->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $teamA->members()->attach($member->id, ['role' => TeamRole::Member->value]);
        $taskA = $this->taskInTeam($teamA);
        $taskA->assignees()->attach($member->id);

        $teamB = Team::factory()->create();
        $teamB->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $teamB->members()->attach($member->id, ['role' => TeamRole::Member->value]);
        $taskB = $this->taskInTeam($teamB);
        $taskB->assignees()->attach($member->id);

        app(RemoveTeamMemberService::class)->execute($owner, $teamA, $member);

        $this->assertDatabaseMissing('task_user', ['task_id' => $taskA->id, 'user_id' => $member->id]);
        $this->assertDatabaseHas('task_user', ['task_id' => $taskB->id, 'user_id' => $member->id]);
    }
}
