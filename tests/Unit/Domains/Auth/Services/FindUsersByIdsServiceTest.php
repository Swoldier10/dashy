<?php

namespace Tests\Unit\Domains\Auth\Services;

use App\Domains\Auth\Services\FindUsersByIdsService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindUsersByIdsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_users(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $users = app(FindUsersByIdsService::class)->execute([$a->id, $b->id]);

        $ids = $users->pluck('id')->all();
        sort($ids);
        $expected = [$a->id, $b->id];
        sort($expected);
        $this->assertSame($expected, $ids);
    }

    public function test_returns_empty_for_empty_ids(): void
    {
        $this->assertCount(0, app(FindUsersByIdsService::class)->execute([]));
    }
}
