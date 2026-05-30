<?php

namespace App\Domains\Chat\Services;

use App\Domains\Chat\Actions\CreateMessageAction;
use App\Domains\Chat\Actions\FindChatAction;
use App\Domains\Chat\Actions\ListMessagesForChatAction;
use App\Domains\Chat\Ai\Services\LlmInputBuilder;
use App\Domains\Chat\Enums\MessageRole;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Codex\DTOs\ChatStreamEvent;
use App\Domains\Codex\Models\CodexConnection;
use App\Domains\Codex\Services\CodexClient;
use App\Domains\Codex\Services\FindCodexConnectionForUserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Compacts the head of a long chat into a single `is_summary=true` message.
 * The original rows stay in the DB so the UI can still render them — only
 * the LlmInputBuilder treats the summary as a hard cutoff, so prompt size
 * stays bounded as chats grow. Idempotent: re-running on a chat that
 * already has a recent summary is harmless (a newer summary supersedes).
 */
final class CompactHistoryService
{
    public const DEFAULT_TRIGGER_THRESHOLD = 30;

    public const DEFAULT_KEEP_TAIL = 8;

    private const SUMMARY_PROMPT = <<<'TXT'
You will receive a long Dashy chat history. Summarise it in 3–5 short
paragraphs that preserve:

- Key decisions and rationale (with the people involved).
- Project / task ids and names that were referenced.
- Any tool calls that were made and their outcomes (created / discarded / failed).
- Any open questions or pending follow-ups.

Write the summary as a neutral third-person recap that the assistant can use
as memory in the next turn. Do not include any preamble — just the summary
text.
TXT;

    public function __construct(
        private FindCodexConnectionForUserService $findConnection,
        private CodexClient $codex,
        private CreateMessageAction $createMessage,
        private LlmInputBuilder $inputBuilder,
        private ListMessagesForChatAction $listMessages,
        private FindChatAction $findChat,
    ) {}

    /**
     * Roll the head of the chat (everything except the trailing $keepTail
     * messages) into one is_summary message. Returns the new summary
     * message, or null when there isn't enough to compact.
     */
    public function execute(
        Chat|int $chat,
        int $triggerThreshold = self::DEFAULT_TRIGGER_THRESHOLD,
        int $keepTail = self::DEFAULT_KEEP_TAIL,
    ): ?Message {
        if (is_int($chat)) {
            $found = $this->findChat->execute($chat);
            if ($found === null) {
                return null;
            }
            $chat = $found;
        }

        $all = $this->listMessages->execute($chat);
        if ($all->count() < $triggerThreshold) {
            return null;
        }

        // Head = everything before the last $keepTail messages.
        $head = $all->slice(0, $all->count() - $keepTail);
        if ($head->isEmpty()) {
            return null;
        }

        $connection = $this->findConnection->execute($chat->user);
        if ($connection === null) {
            return null;
        }

        $inputItems = $this->inputBuilder->build($head);
        if ($inputItems === []) {
            return null;
        }

        try {
            $summary = $this->summarise($connection, $inputItems);
        } catch (Throwable $e) {
            Log::warning('CompactHistoryService failed to summarise chat', [
                'chat_id' => $chat->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if (trim($summary) === '') {
            return null;
        }

        return DB::transaction(fn () => $this->createMessage->execute([
            'chat_id' => $chat->id,
            'role' => MessageRole::Assistant->value,
            'content' => $summary,
            'is_summary' => true,
        ]));
    }

    /**
     * @param  array<int, array<string, mixed>>  $inputItems
     */
    private function summarise(CodexConnection $connection, array $inputItems): string
    {
        $assembled = '';
        foreach ($this->codex->streamChat($connection, $inputItems, self::SUMMARY_PROMPT) as $event) {
            if ($event->type === ChatStreamEvent::TYPE_TEXT_DELTA) {
                $assembled .= (string) $event->text;
            }
        }

        return trim($assembled);
    }
}
