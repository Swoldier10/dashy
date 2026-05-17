<?php

namespace Tests\Unit\Domains\Chat\Services;

use App\Domains\Chat\Enums\MessageRole;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Services\SendMessageService;
use App\Domains\Codex\DTOs\ChatStreamEvent;
use App\Domains\Codex\Exceptions\CodexNotConnectedException;
use App\Domains\Codex\Models\CodexConnection;
use App\Domains\Codex\Services\CodexClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class SendMessageServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_user_message_persists(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);

        $message = app(SendMessageService::class)->saveUserMessage($chat, 'hi there');

        $this->assertSame('hi there', $message->content);
        $this->assertSame(MessageRole::User, $message->role);
    }

    public function test_save_user_message_rejects_empty(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);

        $this->expectException(ValidationException::class);

        app(SendMessageService::class)->saveUserMessage($chat, '');
    }

    public function test_save_assistant_message_persists(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);

        $message = app(SendMessageService::class)->saveAssistantMessage($chat, 'pong');

        $this->assertSame('pong', $message->content);
        $this->assertSame(MessageRole::Assistant, $message->role);
    }

    public function test_stream_assistant_throws_when_not_connected(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);

        $this->expectException(CodexNotConnectedException::class);

        $generator = app(SendMessageService::class)->streamAssistant($chat, $user);
        iterator_to_array($generator);
    }

    public function test_stream_assistant_yields_client_deltas(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);
        CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
            'expires_at' => now()->addHour(),
        ]);
        // seed one user message so $chat->messages() returns content for the LLM call
        $chat->messages()->create(['role' => 'user', 'content' => 'hi']);

        $mockClient = Mockery::mock(CodexClient::class);
        $mockClient->shouldReceive('streamChat')
            ->once()
            ->andReturnUsing(function () {
                yield ChatStreamEvent::textDelta('Hel');
                yield ChatStreamEvent::textDelta('lo');
            });
        $this->app->instance(CodexClient::class, $mockClient);

        $deltas = [];
        foreach (app(SendMessageService::class)->streamAssistant($chat, $user) as $event) {
            $this->assertSame(ChatStreamEvent::TYPE_TEXT_DELTA, $event->type);
            $deltas[] = $event->text;
        }

        $this->assertSame(['Hel', 'lo'], $deltas);
    }
}
