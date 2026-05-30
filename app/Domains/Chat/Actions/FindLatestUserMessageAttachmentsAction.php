<?php

namespace App\Domains\Chat\Actions;

use App\Domains\Chat\Enums\MessageRole;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;

class FindLatestUserMessageAttachmentsAction
{
    /**
     * Raw `attachments` array of the most recent user message in the chat,
     * or an empty array when there is none.
     *
     * @return array<int, mixed>
     */
    public function execute(Chat $chat): array
    {
        // reorder() clears the messages() relation's default orderBy('id' ASC);
        // a plain orderByDesc would append, leaving ASC to win and returning the
        // OLDEST user message instead of the latest.
        $latest = $chat->messages()
            ->where('role', MessageRole::User->value)
            ->reorder('id', 'desc')
            ->first(['attachments']);

        if (! $latest instanceof Message) {
            return [];
        }

        return (array) ($latest->attachments ?? []);
    }
}
