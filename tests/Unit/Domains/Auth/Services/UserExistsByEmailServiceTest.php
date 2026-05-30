<?php

namespace Tests\Unit\Domains\Auth\Services;

use App\Domains\Auth\Services\UserExistsByEmailService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserExistsByEmailServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): UserExistsByEmailService
    {
        return app(UserExistsByEmailService::class);
    }

    public function test_returns_false_for_a_blank_email(): void
    {
        $this->assertFalse($this->service()->execute('   '));
    }

    public function test_returns_false_for_an_unknown_email(): void
    {
        $this->assertFalse($this->service()->execute('nobody@example.com'));
    }

    public function test_returns_true_for_an_existing_email(): void
    {
        User::factory()->create(['email' => 'known@example.com']);

        $this->assertTrue($this->service()->execute('known@example.com'));
    }
}
