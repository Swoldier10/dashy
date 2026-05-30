<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\IsTeamOwnerAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IsTeamOwnerActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_true_for_an_owner(): void
    {
        $team = Team::factory()->create();
        $owner = User::factory()->create();
        $team->members()->attach($owner->id, ['role' => TeamRole::Owner->value]);

        $this->assertTrue((new IsTeamOwnerAction)->execute($team, (int) $owner->id));
    }

    public function test_returns_false_for_a_non_owner_member(): void
    {
        $team = Team::factory()->create();
        $member = User::factory()->create();
        $team->members()->attach($member->id, ['role' => TeamRole::Member->value]);

        $this->assertFalse((new IsTeamOwnerAction)->execute($team, (int) $member->id));
    }

    public function test_returns_false_for_a_non_member(): void
    {
        $team = Team::factory()->create();
        $stranger = User::factory()->create();

        $this->assertFalse((new IsTeamOwnerAction)->execute($team, (int) $stranger->id));
    }
}
