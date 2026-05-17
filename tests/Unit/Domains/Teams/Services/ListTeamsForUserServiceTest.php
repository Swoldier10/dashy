<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\ListTeamsForUserService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListTeamsForUserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_teams_for_the_given_user_only(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $mine = Team::factory()->create(['name' => 'Mine']);
        $theirs = Team::factory()->create(['name' => 'Theirs']);

        $mine->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $theirs->members()->attach($other->id, ['role' => TeamRole::Owner->value]);

        $teams = app(ListTeamsForUserService::class)->execute($user);

        $this->assertCount(1, $teams);
        $this->assertSame('Mine', $teams->first()->name);
    }

    public function test_personal_team_is_first(): void
    {
        $user = User::factory()->create();
        $other = Team::factory()->create(['name' => 'Acme']);
        $personal = Team::factory()->personal()->create(['name' => 'Personal']);

        $other->members()->attach($user->id, ['role' => TeamRole::Owner->value]);
        $personal->members()->attach($user->id, ['role' => TeamRole::Owner->value]);

        $names = app(ListTeamsForUserService::class)->execute($user)->pluck('name')->all();

        $this->assertSame(['Personal', 'Acme'], $names);
    }
}
