<?php

namespace App\Domains\Chat\Actions;

use App\Domains\Chat\Models\Chat;
use App\Models\User;

class FindChatForUserAction
{
    public function execute(User $user, int $chatId): ?Chat
    {
        return Chat::where('user_id', $user->id)->find($chatId);
    }
}
