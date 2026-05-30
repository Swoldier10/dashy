<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\IsTeamMemberAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IsTeamMemberActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_true_for_a_member(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);

        $this->assertTrue((new IsTeamMemberAction)->execute($team, (int) $user->id));
    }

    public function test_returns_false_for_a_non_member(): void
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();

        $this->assertFalse((new IsTeamMemberAction)->execute($team, (int) $user->id));
    }
}
