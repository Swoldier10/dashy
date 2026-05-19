<?php

namespace App\Domains\Chat\Actions;

use App\Domains\Chat\Models\Message;
use App\Models\User;

class FindMessageForUserAction
{
    public function execute(User $user, int $messageId): ?Message
    {
        return Message::query()
            ->whereHas('chat', fn ($q) => $q->where('user_id', $user->id))
            ->find($messageId);
    }
}
