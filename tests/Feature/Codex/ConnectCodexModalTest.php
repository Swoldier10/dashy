<?php

namespace Tests\Feature\Codex;

use App\Domains\Codex\Models\CodexConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ConnectCodexModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_start_kicks_off_device_code_and_stores_state(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Http::fake([
            'https://auth.openai.com/api/accounts/deviceauth/usercode' => Http::response([
                'device_auth_id' => 'dev-1',
                'user_code' => 'ABCD-EFGH',
                'interval' => 5,
            ]),
        ]);

        Livewire::test('codex.connect-codex-modal')
            ->call('start')
            ->assertSet('userCode', 'ABCD-EFGH')
            ->assertSet('deviceAuthId', 'dev-1')
            ->assertSet('isPolling', true)
            ->assertSet('verificationUrl', 'https://auth.openai.com/codex/device')
            ->assertSet('pollIntervalMs', 5000);
    }

    public function test_poll_returns_pending_does_not_persist(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Http::fake([
            'https://auth.openai.com/api/accounts/deviceauth/token' => Http::response('', 403),
        ]);

        Livewire::test('codex.connect-codex-modal')
            ->set('deviceAuthId', 'dev-1')
            ->set('userCode', 'ABCD-EFGH')
            ->set('isPolling', true)
            ->call('poll')
            ->assertSet('isPolling', true);

        $this->assertSame(0, CodexConnection::count());
    }

    public function test_poll_persists_connection_and_dispatches_event_on_success(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Http::fake([
            'https://auth.openai.com/api/accounts/deviceauth/token' => Http::response([
                'authorization_code' => 'auth-1',
                'code_verifier' => 'verifier-1',
            ]),
            'https://auth.openai.com/oauth/token' => Http::response([
                'access_token' => 'access-1',
                'refresh_token' => 'refresh-1',
                'expires_in' => 3600,
            ]),
        ]);

        Livewire::test('codex.connect-codex-modal')
            ->set('deviceAuthId', 'dev-1')
            ->set('userCode', 'ABCD-EFGH')
            ->set('isPolling', true)
            ->call('poll')
            ->assertSet('isPolling', false)
            ->assertDispatched('codex-connected');

        $this->assertSame(1, CodexConnection::count());
        $this->assertSame('access-1', CodexConnection::firstOrFail()->access_token);
    }

    public function test_cancel_clears_state(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('codex.connect-codex-modal')
            ->set('isPolling', true)
            ->set('userCode', 'XXXX')
            ->call('cancel')
            ->assertSet('isPolling', false)
            ->assertSet('userCode', null);
    }
}
