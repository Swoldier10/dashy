<?php

namespace Tests\Unit\Domains\Auth\Actions;

use App\Domains\Auth\Actions\FindUsersByIdsAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindUsersByIdsActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_the_users_matching_the_given_ids(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        User::factory()->create(); // unrelated

        $users = (new FindUsersByIdsAction)->execute([(int) $a->id, (int) $b->id]);

        $this->assertEqualsCanonicalizing([$a->id, $b->id], $users->pluck('id')->all());
    }

    public function test_ignores_unknown_ids(): void
    {
        $a = User::factory()->create();

        $users = (new FindUsersByIdsAction)->execute([(int) $a->id, 999_999]);

        $this->assertSame([$a->id], $users->pluck('id')->all());
    }

    public function test_returns_an_empty_collection_for_an_empty_id_list(): void
    {
        $this->assertTrue((new FindUsersByIdsAction)->execute([])->isEmpty());
    }
}
