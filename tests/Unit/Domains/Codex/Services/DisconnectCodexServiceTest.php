<?php

namespace Tests\Unit\Domains\Codex\Services;

use App\Domains\Codex\Models\CodexConnection;
use App\Domains\Codex\Services\DisconnectCodexService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisconnectCodexServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_connection_when_present(): void
    {
        $user = User::factory()->create();
        CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
        ]);

        app(DisconnectCodexService::class)->execute($user);

        $this->assertSame(0, CodexConnection::count());
    }

    public function test_no_op_when_no_connection(): void
    {
        $user = User::factory()->create();

        app(DisconnectCodexService::class)->execute($user);

        $this->assertSame(0, CodexConnection::count());
    }
}
