<?php

namespace Tests\Unit\Domains\Teams\Services;

use App\Domains\Teams\Enums\TeamRole;
use App\Domains\Teams\Models\Team;
use App\Domains\Teams\Services\ListTeamMemberIdsService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListTeamMemberIdsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_member_ids(): void
    {
        $team = Team::factory()->create();
        $a = User::factory()->create();
        $b = User::factory()->create();
        $team->members()->attach($a->id, ['role' => TeamRole::Owner->value]);
        $team->members()->attach($b->id, ['role' => TeamRole::Member->value]);

        $ids = app(ListTeamMemberIdsService::class)->execute($team);

        sort($ids);
        $expected = [$a->id, $b->id];
        sort($expected);
        $this->assertSame($expected, $ids);
    }
}
