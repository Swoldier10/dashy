<?php

namespace App\Domains\Chat\Actions;

use App\Domains\Chat\Models\Chat;

class CountChatMessagesAction
{
    public function execute(Chat $chat): int
    {
        return $chat->messages()->count();
    }
}
