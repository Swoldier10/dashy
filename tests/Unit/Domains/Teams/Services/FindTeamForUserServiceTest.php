<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\FindTeamForUserService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindTeamForUserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_team_when_member(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);

        $found = app(FindTeamForUserService::class)->execute($user, $team->id);

        $this->assertNotNull($found);
        $this->assertSame($team->id, $found->id);
    }

    public function test_returns_null_for_non_member(): void
    {
        $stranger = User::factory()->create();
        $team = Team::factory()->create();

        $this->assertNull(app(FindTeamForUserService::class)->execute($stranger, $team->id));
    }
}
