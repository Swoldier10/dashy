<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\DeleteOrphanedTeamsForUserService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteOrphanedTeamsForUserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_solo_teams(): void
    {
        $user = User::factory()->create();
        $solo = Team::factory()->create();
        $shared = Team::factory()->create();
        $solo->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $shared->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $shared->members()->attach(User::factory()->create()->id, ['role' => TeamRole::Member->value]);

        app(DeleteOrphanedTeamsForUserService::class)->execute($user);

        $this->assertNull(Team::query()->find($solo->id));
        $this->assertNotNull(Team::query()->find($shared->id));
    }

    public function test_no_op_when_user_has_no_teams(): void
    {
        $user = User::factory()->create();

        app(DeleteOrphanedTeamsForUserService::class)->execute($user);

        $this->assertSame(0, Team::count());
    }

    public function test_deletes_a_solo_personal_team(): void
    {
        $user = User::factory()->create();
        $personal = Team::factory()->personal()->create();
        $personal->members()->attach($user->id, ['role' => TeamRole::Owner->value]);

        app(DeleteOrphanedTeamsForUserService::class)->execute($user);

        $this->assertDatabaseMissing('teams', ['id' => $personal->id]);
    }
}
