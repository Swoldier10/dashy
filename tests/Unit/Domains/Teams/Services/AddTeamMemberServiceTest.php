<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\AddTeamMemberService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AddTeamMemberServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_add_existing_user_as_member(): void
    {
        [$owner, $team] = $this->makeTeamWithOwner();
        $invitee = User::factory()->create(['email' => 'invitee@example.com']);

        $added = app(AddTeamMemberService::class)->execute(
            $owner,
            $team,
            ['email' => 'invitee@example.com'],
        );

        $this->assertTrue($added->is($invitee));
        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $invitee->id,
            'role' => TeamRole::Member->value,
        ]);
    }

    public function test_member_cannot_add(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);

        User::factory()->create(['email' => 'invitee@example.com']);

        $this->expectException(AuthorizationException::class);

        app(AddTeamMemberService::class)->execute(
            $member,
            $team,
            ['email' => 'invitee@example.com'],
        );
    }

    public function test_unknown_email_is_rejected(): void
    {
        [$owner, $team] = $this->makeTeamWithOwner();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No Dashy account with that email.');

        app(AddTeamMemberService::class)->execute(
            $owner,
            $team,
            ['email' => 'nope@example.com'],
        );
    }

    public function test_owner_cannot_add_themselves(): void
    {
        [$owner, $team] = $this->makeTeamWithOwner();

        $this->expectException(ValidationException::class);

        app(AddTeamMemberService::class)->execute(
            $owner,
            $team,
            ['email' => $owner->email],
        );
    }

    public function test_duplicate_member_is_rejected(): void
    {
        [$owner, $team] = $this->makeTeamWithOwner();
        $existing = User::factory()->create(['email' => 'in@example.com']);
        $team->members()->attach($existing->id, ['role' => TeamRole::Member->value]);

        $this->expectException(ValidationException::class);

        app(AddTeamMemberService::class)->execute(
            $owner,
            $team,
            ['email' => 'in@example.com'],
        );
    }

    public function test_email_must_be_valid_format(): void
    {
        [$owner, $team] = $this->makeTeamWithOwner();

        $this->expectException(ValidationException::class);

        app(AddTeamMemberService::class)->execute(
            $owner,
            $team,
            ['email' => 'not-an-email'],
        );
    }

    /** @return array{0: User, 1: Team} */
    private function makeTeamWithOwner(): array
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);

        return [$owner, $team];
    }
}
