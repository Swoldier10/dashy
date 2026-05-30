<?php

namespace Tests\Unit\Domains\Auth\Services;

use App\Domains\Auth\Services\UserSharesTeamWithService;
use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSharesTeamWithServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_true_when_users_share_team(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($a->id, ['role' => TeamRole::Member->value]);
        $team->members()->attach($b->id, ['role' => TeamRole::Member->value]);

        $this->assertTrue(app(UserSharesTeamWithService::class)->execute($a, $b));
    }

    public function test_false_when_no_shared_team(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->assertFalse(app(UserSharesTeamWithService::class)->execute($a, $b));
    }
}
