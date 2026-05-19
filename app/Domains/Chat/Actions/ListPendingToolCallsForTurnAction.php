<?php

namespace App\Domains\Chat\Actions;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use Illuminate\Database\Eloquent\Collection;

class ListPendingToolCallsForTurnAction
{
    /**
     * @return Collection<int, Message>
     */
    public function execute(Chat $chat, int $parentUserMessageId): Collection
    {
        return $chat->messages()
            ->where('parent_user_message_id', $parentUserMessageId)
            ->whereNotNull('tool_call')
            ->get(['tool_call']);
    }
}
