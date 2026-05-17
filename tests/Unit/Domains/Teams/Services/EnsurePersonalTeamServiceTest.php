<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Services\EnsurePersonalTeamService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsurePersonalTeamServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_personal_team_when_user_has_none(): void
    {
        $user = User::factory()->create(['first_name' => 'Pat']);

        $team = app(EnsurePersonalTeamService::class)->execute($user);

        $this->assertTrue($team->personal_team);
        $this->assertSame("Pat's Team", $team->name);
        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
    }

    public function test_idempotent_returns_existing_personal_team(): void
    {
        $user = User::factory()->create();
        $service = app(EnsurePersonalTeamService::class);

        $first = $service->execute($user);
        $second = $service->execute($user);

        $this->assertTrue($first->is($second));
        $this->assertSame(1, $user->teams()->where('teams.personal_team', true)->count());
    }

    public function test_falls_back_to_personal_when_first_name_missing(): void
    {
        $user = User::factory()->create(['first_name' => null, 'name' => '']);

        $team = app(EnsurePersonalTeamService::class)->execute($user);

        $this->assertSame('Personal', $team->name);
    }

    public function test_creator_is_attached_as_owner(): void
    {
        $user = User::factory()->create();

        $team = app(EnsurePersonalTeamService::class)->execute($user);

        $role = $team->members()->whereKey($user->id)->first()->pivot->role;
        $this->assertSame(TeamRole::Owner->value, $role instanceof TeamRole ? $role->value : $role);
    }
}
