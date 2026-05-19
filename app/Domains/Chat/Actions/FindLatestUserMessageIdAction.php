<?php

namespace App\Domains\Chat\Actions;

use App\Domains\Chat\Enums\MessageRole;
use App\Domains\Chat\Models\Chat;

class FindLatestUserMessageIdAction
{
    public function execute(Chat $chat): ?int
    {
        $id = $chat->messages()
            ->where('role', MessageRole::User->value)
            ->orderByDesc('id')
            ->value('id');

        return $id !== null ? (int) $id : null;
    }
}
