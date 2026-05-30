<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\ListTeamMembersService;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListTeamMembersServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): ListTeamMembersService
    {
        return app(ListTeamMembersService::class);
    }

    public function test_returns_members_for_a_team_the_actor_belongs_to_ordered_by_name(): void
    {
        $team = Team::factory()->create();
        $actor = User::factory()->create(['name' => 'Mara']);
        $zoe = User::factory()->create(['name' => 'Zoe']);
        $abby = User::factory()->create(['name' => 'Abby']);
        $team->members()->attach($actor->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($zoe->id, ['role' => TeamRole::Member->value]);
        $team->members()->attach($abby->id, ['role' => TeamRole::Member->value]);

        $members = $this->service()->execute($actor, (int) $team->id);

        $this->assertSame(['Abby', 'Mara', 'Zoe'], $members->pluck('name')->all());
    }

    public function test_throws_when_actor_is_not_a_member(): void
    {
        $team = Team::factory()->create();
        $team->members()->attach(User::factory()->create()->id, ['role' => TeamRole::Owner->value]);
        $stranger = User::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        $this->service()->execute($stranger, (int) $team->id);
    }
}
