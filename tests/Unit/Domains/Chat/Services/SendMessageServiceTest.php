<?php

namespace Tests\Unit\Domains\Chat\Services;

use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Ai\Services\AiToolRegistry;
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

    public function test_oversized_tool_result_is_capped_when_persisted(): void
    {
        $user = User::factory()->create();
        $this->registerAutoReadTool('big_read', ['blob' => str_repeat('x', 20_000)]);

        $event = ChatStreamEvent::toolCallCompleted('fc_big', 'big_read', '{}');
        $payload = app(SendMessageService::class)->dispatchToolCall($user, $event);

        $this->assertSame('executed', $payload['status']);
        $this->assertTrue($payload['result']['truncated']);
        $this->assertArrayHasKey('preview', $payload['result']);
    }

    public function test_normal_tool_result_is_persisted_intact(): void
    {
        $user = User::factory()->create();
        $this->registerAutoReadTool('small_read', ['ok' => true]);

        $event = ChatStreamEvent::toolCallCompleted('fc_small', 'small_read', '{}');
        $payload = app(SendMessageService::class)->dispatchToolCall($user, $event);

        $this->assertSame('executed', $payload['status']);
        $this->assertSame(['ok' => true], $payload['result']);
    }

    public function test_non_encodable_tool_result_becomes_a_safe_marker(): void
    {
        $user = User::factory()->create();
        // INF is not JSON-encodable, so json_encode() returns false.
        $this->registerAutoReadTool('bad_read', ['payload' => INF]);

        $event = ChatStreamEvent::toolCallCompleted('fc_bad', 'bad_read', '{}');
        $payload = app(SendMessageService::class)->dispatchToolCall($user, $event);

        $this->assertSame('executed', $payload['status']);
        $this->assertArrayHasKey('error', $payload['result']);
    }

    /**
     * Register a single fake auto-read tool on a fresh registry so dispatchToolCall
     * runs its execute() inline.
     *
     * @param  array<string, mixed>  $result
     */
    private function registerAutoReadTool(string $name, array $result): void
    {
        $tool = new class($name, $result) implements AiTool
        {
            /** @param array<string, mixed> $result */
            public function __construct(private string $toolName, private array $result) {}

            public function name(): string
            {
                return $this->toolName;
            }

            public function description(): string
            {
                return 'fake';
            }

            public function executionMode(): AiToolExecutionMode
            {
                return AiToolExecutionMode::AutoRead;
            }

            public function parameters(): array
            {
                return [];
            }

            public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
            {
                return AiToolValidationResult::ok([]);
            }

            public function execute(User $user, array $arguments): array
            {
                return $this->result;
            }
        };

        $registry = new AiToolRegistry;
        $registry->register($tool);
        $this->app->instance(AiToolRegistry::class, $registry);
    }
}
