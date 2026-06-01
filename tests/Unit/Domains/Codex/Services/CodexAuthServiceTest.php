<?php

namespace Tests\Unit\Domains\Codex\Services;

use App\Domains\Codex\Exceptions\CodexAuthException;
use App\Domains\Codex\Exceptions\CodexNotConnectedException;
use App\Domains\Codex\Models\CodexConnection;
use App\Domains\Codex\Services\CodexAuthService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CodexAuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): CodexAuthService
    {
        return app(CodexAuthService::class);
    }

    public function test_start_device_code_returns_user_code_and_id(): void
    {
        Http::fake([
            'https://auth.openai.com/api/accounts/deviceauth/usercode' => Http::response([
                'device_auth_id' => 'dev-123',
                'user_code' => 'ABCD-EFGH',
                'interval' => 5,
            ]),
        ]);

        $code = $this->service()->startDeviceCode();

        $this->assertSame('dev-123', $code->deviceAuthId);
        $this->assertSame('ABCD-EFGH', $code->userCode);
        $this->assertSame('https://auth.openai.com/codex/device', $code->verificationUrl);
        $this->assertSame(5, $code->pollIntervalSeconds);
    }

    public function test_start_device_code_throws_on_http_error(): void
    {
        Http::fake([
            'https://auth.openai.com/api/accounts/deviceauth/usercode' => Http::response('boom', 500),
        ]);

        $this->expectException(CodexAuthException::class);
        $this->service()->startDeviceCode();
    }

    public function test_start_device_code_throws_when_response_missing_fields(): void
    {
        Http::fake([
            'https://auth.openai.com/api/accounts/deviceauth/usercode' => Http::response([
                'interval' => 5,
            ]),
        ]);

        $this->expectException(CodexAuthException::class);
        $this->service()->startDeviceCode();
    }

    public function test_poll_returns_null_while_pending(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://auth.openai.com/api/accounts/deviceauth/token' => Http::response('', 403),
        ]);

        $this->assertNull($this->service()->pollForToken($user, 'dev-1', 'ABCD-EFGH'));
        $this->assertSame(0, CodexConnection::count());
    }

    public function test_poll_persists_connection_on_completion(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://auth.openai.com/api/accounts/deviceauth/token' => Http::response([
                'authorization_code' => 'auth-code',
                'code_verifier' => 'verifier-1',
            ]),
            'https://auth.openai.com/oauth/token' => Http::response([
                'access_token' => 'access-1',
                'refresh_token' => 'refresh-1',
                'expires_in' => 3600,
            ]),
        ]);

        $connection = $this->service()->pollForToken($user, 'dev-1', 'ABCD-EFGH');

        $this->assertNotNull($connection);
        $this->assertSame('access-1', $connection->access_token);
        $this->assertSame('refresh-1', $connection->refresh_token);
        $this->assertSame(1, CodexConnection::count());
    }

    public function test_poll_updates_existing_connection(): void
    {
        $user = User::factory()->create();
        $existing = CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'old',
            'refresh_token' => 'old-r',
        ]);

        Http::fake([
            'https://auth.openai.com/api/accounts/deviceauth/token' => Http::response([
                'authorization_code' => 'auth-code',
                'code_verifier' => 'verifier-1',
            ]),
            'https://auth.openai.com/oauth/token' => Http::response([
                'access_token' => 'new',
                'refresh_token' => 'new-r',
                'expires_in' => 3600,
            ]),
        ]);

        $this->service()->pollForToken($user, 'dev-1', 'ABCD-EFGH');

        $this->assertSame(1, CodexConnection::count());
        $this->assertSame('new', $existing->fresh()->access_token);
    }

    public function test_poll_throws_on_unexpected_http_error(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://auth.openai.com/api/accounts/deviceauth/token' => Http::response('boom', 500),
        ]);

        $this->expectException(CodexAuthException::class);
        $this->service()->pollForToken($user, 'dev-1', 'ABCD-EFGH');
    }

    public function test_ensure_fresh_token_passes_through_when_not_expired(): void
    {
        $user = User::factory()->create();
        $connection = CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
            'refresh_token' => 'r',
            'expires_at' => now()->addHour(),
        ]);

        $result = $this->service()->ensureFreshToken($connection);

        $this->assertSame('a', $result->access_token);
    }

    public function test_ensure_fresh_token_refreshes_when_expired(): void
    {
        $user = User::factory()->create();
        $connection = CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'old',
            'refresh_token' => 'r-token',
            'expires_at' => now()->subMinute(),
        ]);

        Http::fake([
            'https://auth.openai.com/oauth/token' => Http::response([
                'access_token' => 'fresh',
                'refresh_token' => 'fresh-r',
                'expires_in' => 3600,
            ]),
        ]);

        $result = $this->service()->ensureFreshToken($connection);

        $this->assertSame('fresh', $result->access_token);
    }

    public function test_ensure_fresh_token_deletes_connection_and_throws_when_refresh_fails(): void
    {
        $user = User::factory()->create();
        CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'old',
            'refresh_token' => 'r',
            'expires_at' => now()->subMinute(),
        ]);

        Http::fake([
            'https://auth.openai.com/oauth/token' => Http::response('oops', 500),
        ]);

        $this->expectException(CodexNotConnectedException::class);

        try {
            $this->service()->ensureFreshToken(CodexConnection::firstOrFail());
        } finally {
            $this->assertSame(0, CodexConnection::count());
        }
    }

    public function test_forget_deletes_the_connection(): void
    {
        $user = User::factory()->create();
        $connection = CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
            'expires_at' => now()->addHour(),
        ]);

        $this->service()->forget($connection);

        $this->assertSame(0, CodexConnection::count());
    }

    public function test_poll_maps_known_device_code_error_to_a_specific_message(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://auth.openai.com/api/accounts/deviceauth/token' => Http::response(['error' => 'access_denied'], 400),
        ]);

        try {
            $this->service()->pollForToken($user, 'dev-1', 'ABCD-EFGH');
            $this->fail('Expected CodexAuthException.');
        } catch (CodexAuthException $e) {
            $this->assertStringContainsString('denied', strtolower($e->getMessage()));
        }
    }
}
