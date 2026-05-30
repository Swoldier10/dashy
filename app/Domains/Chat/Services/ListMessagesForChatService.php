<?php

namespace App\Domains\Chat\Services;

use App\Domains\Chat\Actions\ListMessagesForChatAction;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use Illuminate\Database\Eloquent\Collection;

final class ListMessagesForChatService
{
    public function __construct(
        private ListMessagesForChatAction $list,
    ) {}

    /**
     * @param  list<string>  $columns
     * @return Collection<int, Message>
     */
    public function execute(Chat $chat, array $columns = ['*']): Collection
    {
        return $this->list->execute($chat, $columns);
    }
}
