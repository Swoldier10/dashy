<?php

namespace Tests\Unit\Domains\Auth\Services;

use App\Domains\Auth\Services\FindUserByIdService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindUserByIdServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): FindUserByIdService
    {
        return app(FindUserByIdService::class);
    }

    public function test_returns_the_user_by_id(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->service()->execute((int) $user->id)->is($user));
    }

    public function test_returns_null_for_an_unknown_id(): void
    {
        $this->assertNull($this->service()->execute(999_999));
    }
}
