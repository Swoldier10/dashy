<?php

namespace App\Domains\Chat\Actions;

use App\Domains\Chat\Models\Chat;

class FindChatAction
{
    public function execute(int $id): ?Chat
    {
        return Chat::query()->find($id);
    }
}
