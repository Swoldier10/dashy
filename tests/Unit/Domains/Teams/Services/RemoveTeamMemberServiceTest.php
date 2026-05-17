<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\RemoveTeamMemberService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RemoveTeamMemberServiceTest extends TestCase
{
    use RefreshDatabase;

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
}
