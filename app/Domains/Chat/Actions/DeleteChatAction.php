<?php

namespace App\Domains\Chat\Actions;

use App\Domains\Chat\Models\Chat;

class DeleteChatAction
{
    public function execute(Chat $chat): void
    {
        $chat->delete();
    }
}
