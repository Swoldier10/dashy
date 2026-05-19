<?php

namespace App\Domains\Chat\Actions;

use App\Domains\Chat\Models\Chat;

class HasRecentChatSummaryAction
{
    public function execute(Chat $chat, int $tailLimit): bool
    {
        return $chat->messages()
            ->latest('id')
            ->limit($tailLimit)
            ->where('is_summary', true)
            ->exists();
    }
}
