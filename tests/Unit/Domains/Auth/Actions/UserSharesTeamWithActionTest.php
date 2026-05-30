<?php

namespace Tests\Unit\Domains\Auth\Actions;

use App\Domains\Auth\Actions\UserSharesTeamWithAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSharesTeamWithActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_true_when_users_share_a_team(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($a->id, ['role' => TeamRole::Member->value]);
        $team->members()->attach($b->id, ['role' => TeamRole::Member->value]);

        $this->assertTrue((new UserSharesTeamWithAction)->execute($a, $b));
    }

    public function test_returns_false_when_users_do_not_share_a_team(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $teamA = Team::factory()->create();
        $teamB = Team::factory()->create();
        $teamA->members()->attach($a->id, ['role' => TeamRole::Member->value]);
        $teamB->members()->attach($b->id, ['role' => TeamRole::Member->value]);

        $this->assertFalse((new UserSharesTeamWithAction)->execute($a, $b));
    }
}
