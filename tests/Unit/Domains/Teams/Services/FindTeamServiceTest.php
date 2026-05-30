<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\FindTeamService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindTeamServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_find(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => TeamRole::Member->value]);

        $found = app(FindTeamService::class)->execute($user, $team->id);

        $this->assertTrue($team->is($found));
    }

    public function test_stranger_cannot_find(): void
    {
        $stranger = User::factory()->create();
        $team = Team::factory()->create();

        $this->expectException(AuthorizationException::class);

        app(FindTeamService::class)->execute($stranger, $team->id);
    }
}
