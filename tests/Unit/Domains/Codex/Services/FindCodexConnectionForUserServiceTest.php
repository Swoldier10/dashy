<?php

namespace Tests\Unit\Domains\Codex\Services;

use App\Domains\Codex\Models\CodexConnection;
use App\Domains\Codex\Services\FindCodexConnectionForUserService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindCodexConnectionForUserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_connection_when_present(): void
    {
        $user = User::factory()->create();
        $connection = CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'tok',
            'refresh_token' => 'r',
            'expires_at' => now()->addHour(),
            'account_email' => 'x@example.com',
        ]);

        $found = app(FindCodexConnectionForUserService::class)->execute($user);

        $this->assertNotNull($found);
        $this->assertTrue($connection->is($found));
    }

    public function test_returns_null_when_absent(): void
    {
        $user = User::factory()->create();

        $this->assertNull(app(FindCodexConnectionForUserService::class)->execute($user));
    }
}
