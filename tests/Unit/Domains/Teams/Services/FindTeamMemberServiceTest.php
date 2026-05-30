<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\FindTeamMemberService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindTeamMemberServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_member_when_actor_is_team_member(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($actor->id, ['role' => TeamRole::Member->value]);
        $team->members()->attach($target->id, ['role' => TeamRole::Member->value]);

        $member = app(FindTeamMemberService::class)->execute($actor, $team, $target->id);

        $this->assertNotNull($member);
        $this->assertTrue($target->is($member));
    }

    public function test_returns_null_when_target_is_not_in_team(): void
    {
        $actor = User::factory()->create();
        $stranger = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($actor->id, ['role' => TeamRole::Member->value]);

        $this->assertNull(
            app(FindTeamMemberService::class)->execute($actor, $team, $stranger->id),
        );
    }

    public function test_throws_when_actor_cannot_view_team(): void
    {
        $stranger = User::factory()->create();
        $target = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($target->id, ['role' => TeamRole::Member->value]);

        $this->expectException(AuthorizationException::class);

        app(FindTeamMemberService::class)->execute($stranger, $team, $target->id);
    }
}
