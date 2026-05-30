<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\FindPersonalTeamForUserAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindPersonalTeamForUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_the_users_personal_team(): void
    {
        $user = User::factory()->create();
        $personal = Team::factory()->create(['personal_team' => true]);
        $shared = Team::factory()->create(['personal_team' => false]);
        $personal->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $shared->members()->attach($user->id, ['role' => TeamRole::Member->value]);

        $found = (new FindPersonalTeamForUserAction)->execute($user);

        $this->assertNotNull($found);
        $this->assertTrue($found->is($personal));
    }

    public function test_returns_null_when_user_has_no_personal_team(): void
    {
        $user = User::factory()->create();
        $shared = Team::factory()->create(['personal_team' => false]);
        $shared->members()->attach($user->id, ['role' => TeamRole::Member->value]);

        $this->assertNull((new FindPersonalTeamForUserAction)->execute($user));
    }
}
