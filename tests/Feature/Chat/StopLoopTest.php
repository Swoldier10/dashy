<?php

namespace Tests\Feature\Chat;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Codex\DTOs\ChatStreamEvent;
use App\Domains\Codex\Models\CodexConnection;
use App\Domains\Codex\Services\CodexClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

/**
 * Phase 2: the Stop button halts the agentic loop between iterations.
 * Sending a fresh user message clears the stop flag.
 */
class StopLoopTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_stop_persists_flag_and_halts_next_iteration(): void
    {
        $user = User::factory()->create();
        CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
            'expires_at' => now()->addHour(),
        ]);
        $this->actingAs($user);

        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->andReturnUsing(function () {
            yield ChatStreamEvent::textDelta('reply');
        });
        $this->app->instance(CodexClient::class, $mock);

        $component = Livewire::test('chat.chat-panel')
            ->set('message', 'hi')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $chat = Chat::first();
        $this->assertNotNull($chat);
        $this->assertNull($chat->stop_requested_at);

        // User hits Stop. Stop flag persists.
        $component->call('requestStop');
        $this->assertNotNull($chat->fresh()->stop_requested_at);

        // The next iteration would have re-rendered the assistant — but with
        // the stop flag set, processAssistantReply must bail before any new
        // assistant message is persisted.
        $beforeCount = Message::where('role', 'assistant')->count();
        $component->call('processAssistantReply');
        $afterCount = Message::where('role', 'assistant')->count();
        $this->assertSame($beforeCount, $afterCount, 'no new assistant message after stop.');
    }

    public function test_new_user_message_clears_stop_flag(): void
    {
        $user = User::factory()->create();
        CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
            'expires_at' => now()->addHour(),
        ]);
        $this->actingAs($user);

        $mock = Mockery::mock(CodexClient::class);
        $mock->shouldReceive('streamChat')->andReturnUsing(function () {
            yield ChatStreamEvent::textDelta('reply');
        });
        $this->app->instance(CodexClient::class, $mock);

        $component = Livewire::test('chat.chat-panel')
            ->set('message', 'first message')
            ->call('sendMessage')
            ->call('processAssistantReply');

        $component->call('requestStop');
        $chat = Chat::first();
        $this->assertNotNull($chat->fresh()->stop_requested_at);

        // A new user message clears the stop request.
        $component->set('message', 'second message')->call('sendMessage');
        $this->assertNull($chat->fresh()->stop_requested_at);
    }
}
