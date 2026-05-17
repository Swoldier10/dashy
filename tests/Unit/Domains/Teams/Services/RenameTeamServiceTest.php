<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\RenameTeamService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RenameTeamServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_rename_team(): void
    {
        [$owner, $team] = $this->makeTeamWithOwner();

        $renamed = app(RenameTeamService::class)->execute($owner, $team, ['name' => 'New Name']);

        $this->assertSame('New Name', $renamed->name);
    }

    public function test_owner_can_rename_personal_team(): void
    {
        $owner = User::factory()->create();
        $team = Team::factory()->personal()->create(['name' => 'Old']);
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);

        $renamed = app(RenameTeamService::class)->execute($owner, $team, ['name' => 'My Place']);

        $this->assertSame('My Place', $renamed->name);
        $this->assertTrue($renamed->personal_team);
    }

    public function test_member_cannot_rename(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create(['name' => 'Stays']);
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);

        $this->expectException(AuthorizationException::class);

        app(RenameTeamService::class)->execute($member, $team, ['name' => 'Hostile']);
    }

    public function test_name_validation(): void
    {
        [$owner, $team] = $this->makeTeamWithOwner();

        $this->expectException(ValidationException::class);

        app(RenameTeamService::class)->execute($owner, $team, ['name' => '']);
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
