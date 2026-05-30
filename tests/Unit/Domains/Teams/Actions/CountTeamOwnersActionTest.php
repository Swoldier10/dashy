<?php

namespace Tests\Unit\Domains\Teams\Actions;

use App\Domains\Teams\Actions\CountTeamOwnersAction;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountTeamOwnersActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_counts_only_owners(): void
    {
        $team = Team::factory()->create();
        $team->members()->attach(User::factory()->create()->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach(User::factory()->create()->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach(User::factory()->create()->id, ['role' => TeamRole::Member->value]);

        $this->assertSame(2, (new CountTeamOwnersAction)->execute($team));
    }

    public function test_returns_zero_when_no_owners(): void
    {
        $team = Team::factory()->create();
        $team->members()->attach(User::factory()->create()->id, ['role' => TeamRole::Member->value]);

        $this->assertSame(0, (new CountTeamOwnersAction)->execute($team));
    }
}
