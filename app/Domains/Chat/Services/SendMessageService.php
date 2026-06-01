<?php

namespace App\Domains\Chat\Services;

use App\Domains\Chat\Actions\CountChatMessagesAction;
use App\Domains\Chat\Actions\CreateMessageAction;
use App\Domains\Chat\Actions\HasRecentChatSummaryAction;
use App\Domains\Chat\Actions\ListMessagesForChatAction;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Ai\Services\AiSystemPromptBuilder;
use App\Domains\Chat\Ai\Services\AiToolRegistry;
use App\Domains\Chat\Ai\Services\LlmInputBuilder;
use App\Domains\Chat\Enums\MessageRole;
use App\Domains\Chat\Jobs\CompactHistoryJob;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Codex\DTOs\ChatStreamEvent;
use App\Domains\Codex\Exceptions\CodexNotConnectedException;
use App\Domains\Codex\Services\CodexClient;
use App\Domains\Codex\Services\FindCodexConnectionForUserService;
use App\Models\User;
use Generator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

final class SendMessageService
{
    /**
     * Per-user cap on new chat messages per minute. Guards against a single
     * user (or a script) fanning out unbounded LLM/embedding calls — which
     * would blow up cost and trip the provider's rate limit for every tenant.
     */
    private const MAX_MESSAGES_PER_MINUTE = 30;

    /**
     * Hard cap on the JSON size of a tool result persisted to
     * `messages.tool_call`. The model's *view* is already truncated in
     * LlmInputBuilder; this stops a pathological tool result from bloating or
     * breaking the row insert.
     */
    private const MAX_TOOL_RESULT_BYTES = 16000;

    public function __construct(
        private CreateMessageAction $createMessage,
        private CodexClient $codex,
        private FindCodexConnectionForUserService $findConnection,
        private AiSystemPromptBuilder $promptBuilder,
        private AiToolRegistry $toolRegistry,
        private LlmInputBuilder $inputBuilder,
        private CountChatMessagesAction $countMessages,
        private HasRecentChatSummaryAction $hasRecentSummary,
        private ListMessagesForChatAction $listMessages,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $attachments
     */
    public function saveUserMessage(Chat $chat, string $content, array $attachments = []): Message
    {
        $this->enforceSendRateLimit((int) $chat->user_id);

        Validator::make([
            'content' => $content,
            'attachments' => $attachments,
        ], [
            'content' => ['nullable', 'string', 'max:8000'],
            'attachments' => ['array', 'max:5'],
            'attachments.*.type' => ['required', 'in:image,audio'],
            'attachments.*.path' => ['required', 'string'],
            'attachments.*.url' => ['required', 'string'],
        ])->validate();

        if (trim($content) === '' && $attachments === []) {
            throw ValidationException::withMessages([
                'content' => __('Message cannot be empty.'),
            ]);
        }

        $message = DB::transaction(fn () => $this->createMessage->execute([
            'chat_id' => $chat->id,
            'role' => MessageRole::User->value,
            'content' => $content,
            'attachments' => $attachments !== [] ? $attachments : null,
        ]));

        $this->maybeDispatchCompaction($chat);

        return $message;
    }

    /**
     * Throttle new messages per user. Uses the cache-backed rate limiter
     * (Redis in production once cache moves off the database driver), so the
     * counter is atomic and shared across web workers.
     */
    private function enforceSendRateLimit(int $userId): void
    {
        $key = 'chat-send:'.$userId;

        if (RateLimiter::tooManyAttempts($key, self::MAX_MESSAGES_PER_MINUTE)) {
            throw ValidationException::withMessages([
                'content' => __('You\'re sending messages too quickly. Please wait :seconds seconds.', [
                    'seconds' => RateLimiter::availableIn($key),
                ]),
            ]);
        }

        RateLimiter::hit($key, 60);
    }

    /**
     * When a chat crosses the compaction threshold AND there is no recent
     * summary (none in the last N messages), queue a CompactHistoryJob so
     * the next LLM round runs against a compacted history. Idempotent — the
     * job itself bails when nothing to compact, so duplicate dispatches are
     * cheap.
     */
    private function maybeDispatchCompaction(Chat $chat): void
    {
        $totalMessages = $this->countMessages->execute($chat);
        if ($totalMessages < CompactHistoryService::DEFAULT_TRIGGER_THRESHOLD) {
            return;
        }

        $hasRecentSummary = $this->hasRecentSummary->execute(
            $chat,
            CompactHistoryService::DEFAULT_KEEP_TAIL + 2,
        );
        if ($hasRecentSummary) {
            return;
        }

        CompactHistoryJob::dispatch($chat->id);
    }

    /**
     * Stream assistant events. Yields ChatStreamEvent objects; the caller
     * handles text deltas (UI streaming) and tool-call events.
     *
     * @param  array{type: string, id?: int, name?: string}|null  $screen
     *                                                                     viewport hint forwarded to the system-prompt builder so the
     *                                                                     model can resolve "this task" / "here" without ambiguity.
     * @return Generator<int, ChatStreamEvent>
     */
    public function streamAssistant(Chat $chat, User $user, ?array $screen = null): Generator
    {
        $connection = $this->findConnection->execute($user);
        if ($connection === null) {
            throw new CodexNotConnectedException;
        }

        $inputItems = $this->inputBuilder->build(
            $this->listMessages->execute($chat, ['id', 'role', 'content', 'attachments', 'tool_call', 'is_summary'])
        );

        $instructions = $this->promptBuilder->build($user, $screen);
        $tools = $this->toolRegistry->schemas();

        yield from $this->codex->streamChat($connection, $inputItems, $instructions, $tools);
    }

    /**
     * Translate a completed tool-call stream event into the JSON payload that
     * lives on `messages.tool_call`. Validation errors yield `status=failed`;
     * a valid call yields `status=pending` and waits for the user to confirm
     * via ConfirmToolCallService.
     *
     * @return array<string, mixed>
     */
    public function buildToolCallPayload(User $user, ChatStreamEvent $event, ?Chat $chat = null): array
    {
        $args = json_decode($event->argumentsJson ?? '', true);
        if (! is_array($args)) {
            $args = [];
        }

        $tool = $this->toolRegistry->find((string) $event->name);
        if ($tool === null) {
            return [
                'tool_call_id' => $event->callId,
                'name' => $event->name,
                'arguments' => $args,
                'status' => 'failed',
                'validation_errors' => [sprintf('Tool "%s" is not registered.', $event->name)],
            ];
        }

        $result = $tool->validate($user, $args, $chat);

        if ($result->valid) {
            return [
                'tool_call_id' => $event->callId,
                'name' => $event->name,
                'arguments' => $result->normalized,
                'status' => 'pending',
            ];
        }

        return [
            'tool_call_id' => $event->callId,
            'name' => $event->name,
            'arguments' => $args,
            'status' => 'failed',
            'validation_errors' => $result->errors,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $toolCall
     */
    public function saveAssistantMessage(
        Chat $chat,
        string $content,
        ?array $toolCall = null,
        ?int $parentUserMessageId = null,
    ): Message {
        return DB::transaction(fn () => $this->createMessage->execute([
            'chat_id' => $chat->id,
            'parent_user_message_id' => $parentUserMessageId,
            'role' => MessageRole::Assistant->value,
            'content' => $content,
            'tool_call' => $toolCall,
        ]));
    }

    /**
     * Route a completed tool-call stream event through the registry: validate,
     * then execute now (auto_read tools) or leave pending for user confirmation
     * (confirm_write / structural). Returns the payload to persist on the
     * message's `tool_call` column.
     *
     * @return array<string, mixed>
     */
    public function dispatchToolCall(User $user, ChatStreamEvent $event, ?Chat $chat = null): array
    {
        $payload = $this->buildToolCallPayload($user, $event, $chat);

        if ($payload['status'] !== 'pending') {
            // failed-validation calls stay failed; nothing to execute.
            return $payload;
        }

        $tool = $this->toolRegistry->find((string) ($payload['name'] ?? ''));
        if ($tool === null) {
            return $payload;
        }

        $mode = $tool->executionMode();

        // Auto-read tools and the structural `plan` tool resolve inline: they
        // never need user interaction, so the runtime executes them now and
        // feeds the result back into the next LLM iteration. ConfirmWrite
        // tools and the interactive structural ask_user_choice stay pending.
        $runsAtDispatch = $mode === AiToolExecutionMode::AutoRead
            || ($mode === AiToolExecutionMode::Structural && ($payload['name'] ?? null) === 'plan');

        if ($runsAtDispatch) {
            try {
                $result = $tool->execute($user, $payload['arguments']);
                $payload['status'] = 'executed';
                $payload['result'] = $this->capToolResult($result);
            } catch (Throwable $e) {
                Log::warning('Inline tool execution failed', [
                    'tool' => $payload['name'] ?? null,
                    'error' => $e->getMessage(),
                ]);
                $payload['status'] = 'failed';
                $payload['validation_errors'] = [$e->getMessage()];
            }
        }

        // Everything else stays pending; the UI drives them.
        return $payload;
    }

    /**
     * Replace a pathologically large tool result with a small truncated marker
     * before it is persisted, so a runaway payload can't bloat or break the
     * `messages.tool_call` JSON column.
     */
    private function capToolResult(mixed $result): mixed
    {
        $encoded = json_encode($result);

        // A non-encodable result (resource / closure / circular ref) would be
        // silently mangled by the model's array cast on persist — replace it
        // with a safe marker instead.
        if ($encoded === false) {
            return ['error' => __('Tool result could not be encoded.')];
        }

        if (strlen($encoded) > self::MAX_TOOL_RESULT_BYTES) {
            return [
                'truncated' => true,
                'preview' => Str::limit($encoded, 2000),
            ];
        }

        return $result;
    }
}
