<?php

namespace App\Domains\Chat\Actions;

use App\Domains\Chat\Models\Chat;

class ListChatAttachmentPathsAction
{
    /**
     * @return list<string>
     */
    public function execute(Chat $chat): array
    {
        return $chat->messages()
            ->whereNotNull('attachments')
            ->pluck('attachments')
            ->flatten(1)
            ->pluck('path')
            ->filter()
            ->values()
            ->all();
    }
}
