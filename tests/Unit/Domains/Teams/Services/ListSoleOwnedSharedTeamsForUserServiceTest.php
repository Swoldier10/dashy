<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\ListSoleOwnedSharedTeamsForUserService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListSoleOwnedSharedTeamsForUserServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): ListSoleOwnedSharedTeamsForUserService
    {
        return app(ListSoleOwnedSharedTeamsForUserService::class);
    }

    public function test_returns_teams_where_user_is_sole_owner_and_others_depend(): void
    {
        $user = User::factory()->create();
        $blocking = Team::factory()->create(['name' => 'Blocking']);
        $blocking->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $blocking->members()->attach(User::factory()->create()->id, ['role' => TeamRole::Member->value]);

        $result = $this->service()->execute($user);

        $this->assertCount(1, $result);
        $this->assertSame('Blocking', $result->first()->name);
    }

    public function test_excludes_solo_teams_with_no_other_members(): void
    {
        $user = User::factory()->create();
        $solo = Team::factory()->create();
        $solo->members()->attach($user->id, ['role' => TeamRole::Owner->value]);

        $this->assertTrue($this->service()->execute($user)->isEmpty());
    }

    public function test_excludes_teams_with_a_co_owner(): void
    {
        $user = User::factory()->create();
        $coOwned = Team::factory()->create();
        $coOwned->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $coOwned->members()->attach(User::factory()->create()->id, ['role' => TeamRole::Owner->value]);

        $this->assertTrue($this->service()->execute($user)->isEmpty());
    }

    public function test_excludes_teams_where_user_is_only_a_member(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);
        $team->members()->attach(User::factory()->create()->id, ['role' => TeamRole::Owner->value]);

        $this->assertTrue($this->service()->execute($user)->isEmpty());
    }
}
