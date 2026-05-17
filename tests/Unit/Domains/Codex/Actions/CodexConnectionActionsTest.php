<?php

namespace Tests\Unit\Domains\Codex\Actions;

use App\Domains\Codex\Actions\CreateCodexConnectionAction;
use App\Domains\Codex\Actions\DeleteCodexConnectionAction;
use App\Domains\Codex\Actions\FindCodexConnectionForUserAction;
use App\Domains\Codex\Actions\UpdateCodexConnectionAction;
use App\Domains\Codex\Models\CodexConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CodexConnectionActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_persists_attributes(): void
    {
        $user = User::factory()->create();

        $connection = (new CreateCodexConnectionAction)->execute([
            'user_id' => $user->id,
            'access_token' => 'access-1',
            'refresh_token' => 'refresh-1',
            'expires_at' => now()->addHour(),
            'scope' => 'chat',
            'account_email' => 'codex@example.com',
            'account_id' => 'codex-id-1',
        ]);

        $this->assertSame('access-1', $connection->access_token);
        $this->assertSame('refresh-1', $connection->refresh_token);
        $this->assertSame('codex@example.com', $connection->account_email);
        $this->assertDatabaseHas('codex_connections', ['user_id' => $user->id]);
    }

    public function test_update_force_fills_attributes(): void
    {
        $user = User::factory()->create();
        $connection = (new CreateCodexConnectionAction)->execute([
            'user_id' => $user->id,
            'access_token' => 'old',
        ]);

        $updated = (new UpdateCodexConnectionAction)->execute($connection, [
            'access_token' => 'new',
            'expires_at' => now()->addMinutes(30),
        ]);

        $this->assertSame('new', $updated->access_token);
    }

    public function test_delete_removes_row(): void
    {
        $user = User::factory()->create();
        $connection = (new CreateCodexConnectionAction)->execute([
            'user_id' => $user->id,
            'access_token' => 'a',
        ]);

        (new DeleteCodexConnectionAction)->execute($connection);

        $this->assertDatabaseMissing('codex_connections', ['id' => $connection->id]);
    }

    public function test_find_returns_match_or_null(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $connection = (new CreateCodexConnectionAction)->execute([
            'user_id' => $user->id,
            'access_token' => 'a',
        ]);

        $this->assertNotNull((new FindCodexConnectionForUserAction)->execute($user));
        $this->assertNull((new FindCodexConnectionForUserAction)->execute($other));
    }

    public function test_tokens_are_encrypted_at_rest(): void
    {
        $user = User::factory()->create();
        (new CreateCodexConnectionAction)->execute([
            'user_id' => $user->id,
            'access_token' => 'plain-secret-123',
            'refresh_token' => 'plain-refresh-456',
        ]);

        $row = \DB::table('codex_connections')->where('user_id', $user->id)->first();
        $this->assertNotSame('plain-secret-123', $row->access_token);
        $this->assertNotSame('plain-refresh-456', $row->refresh_token);
    }
}
