<?php

namespace App\Domains\Chat\Actions;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use Illuminate\Database\Eloquent\Collection;

class ListMessagesForChatAction
{
    /**
     * @param  list<string>  $columns
     * @return Collection<int, Message>
     */
    public function execute(Chat $chat, array $columns = ['*']): Collection
    {
        return $chat->messages()->get($columns);
    }
}
