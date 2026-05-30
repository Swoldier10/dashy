<?php

namespace Tests\Unit\Domains\Auth\Actions;

use App\Domains\Auth\Actions\FindUserByIdAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindUserByIdActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_the_user_by_id(): void
    {
        $user = User::factory()->create();

        $this->assertTrue((new FindUserByIdAction)->execute((int) $user->id)->is($user));
    }

    public function test_returns_null_for_an_unknown_id(): void
    {
        $this->assertNull((new FindUserByIdAction)->execute(999_999));
    }
}
