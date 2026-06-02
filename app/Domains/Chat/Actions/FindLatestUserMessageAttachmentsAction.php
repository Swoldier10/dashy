<?php

namespace App\Domains\Chat\Actions;

use App\Domains\Chat\Enums\MessageRole;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;

class FindLatestUserMessageAttachmentsAction
{
    /**
     * Raw `attachments` array of the most recent user message that actually
     * carries attachments, or an empty array when none does.
     *
     * Scanning back (rather than taking strictly the latest user message)
     * survives an intermediate user message with no attachment — e.g. the user
     * uploads an image, the assistant asks `ask_user_choice`, the user answers
     * in a plain text message, then the write tool fires. Without this, the
     * image silently drops off the created task.
     *
     * @return array<int, mixed>
     */
    public function execute(Chat $chat): array
    {
        // reorder() clears the messages() relation's default orderBy('id' ASC);
        // a plain orderByDesc would append, leaving ASC to win and returning the
        // OLDEST user message instead of the latest. whereNotNull prunes
        // text-only messages (stored with null attachments) at the DB level; the
        // closure then skips any non-null-but-empty array so the scan continues
        // to the real image. The limit bounds memory and how far back we reach.
        $message = $chat->messages()
            ->where('role', MessageRole::User->value)
            ->whereNotNull('attachments')
            ->reorder('id', 'desc')
            ->limit(20)
            ->get(['id', 'attachments'])
            ->first(fn (Message $m): bool => is_array($m->attachments) && $m->attachments !== []);

        return $message instanceof Message ? (array) $message->attachments : [];
    }
}
