<?php

namespace App\Domains\Chat\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * A chat message's content changed ($deleted = false) or was deleted
 * ($deleted = true). Emitted by the Chat domain; Search listens to keep its
 * embedding index in sync. Only content-bearing messages are emitted on save
 * (summaries / tool-call-only / empty messages are not searchable) — that
 * "is this message content?" judgement is a Chat concern.
 */
final class MessageContentChanged
{
    use Dispatchable;

    public function __construct(
        public int $messageId,
        public bool $deleted = false,
    ) {}
}
