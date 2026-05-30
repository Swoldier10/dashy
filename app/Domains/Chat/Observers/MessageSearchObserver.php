<?php

namespace App\Domains\Chat\Observers;

use App\Domains\Chat\Events\MessageContentChanged;
use App\Domains\Chat\Models\Message;

/**
 * Emits a domain event when a chat message's content changes. Summary
 * placeholders, tool-call-only messages, and empty messages carry no
 * searchable text, so they are not announced on save (a Chat-domain
 * judgement) — but deletions always fire so any index row is cleaned up.
 */
final class MessageSearchObserver
{
    public function saved(Message $message): void
    {
        if ($this->isSearchableContent($message)) {
            MessageContentChanged::dispatch((int) $message->getKey(), false);
        }
    }

    public function deleted(Message $message): void
    {
        MessageContentChanged::dispatch((int) $message->getKey(), true);
    }

    private function isSearchableContent(Message $message): bool
    {
        return ! $message->is_summary
            && trim((string) $message->content) !== ''
            && $message->tool_call === null;
    }
}
