<?php

namespace Tests\Unit\Domains\Auth\Services;

use App\Domains\Auth\Services\LookupUserByEmailService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LookupUserByEmailServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): LookupUserByEmailService
    {
        return app(LookupUserByEmailService::class);
    }

    public function test_returns_the_user_unscoped(): void
    {
        $user = User::factory()->create(['email' => 'any@example.com']);

        $this->assertTrue($this->service()->execute('any@example.com')->is($user));
    }

    public function test_returns_null_for_an_unknown_email(): void
    {
        $this->assertNull($this->service()->execute('nobody@example.com'));
    }
}
