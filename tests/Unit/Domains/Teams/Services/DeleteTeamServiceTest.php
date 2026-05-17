<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\DeleteTeamService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DeleteTeamServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_delete_non_personal_team(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);

        app(DeleteTeamService::class)->execute($owner, $team);

        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    }

    public function test_personal_team_cannot_be_deleted(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->personal()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);

        $this->expectException(ValidationException::class);

        try {
            app(DeleteTeamService::class)->execute($owner, $team);
        } finally {
            $this->assertDatabaseHas('teams', ['id' => $team->id]);
        }
    }

    public function test_member_cannot_delete_team(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);

        $this->expectException(AuthorizationException::class);

        try {
            app(DeleteTeamService::class)->execute($member, $team);
        } finally {
            $this->assertDatabaseHas('teams', ['id' => $team->id]);
        }
    }
}
