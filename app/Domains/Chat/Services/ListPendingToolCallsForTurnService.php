<?php

namespace App\Domains\Chat\Services;

use App\Domains\Chat\Actions\ListPendingToolCallsForTurnAction;
use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use Illuminate\Database\Eloquent\Collection;

final class ListPendingToolCallsForTurnService
{
    public function __construct(
        private ListPendingToolCallsForTurnAction $list,
    ) {}

    /**
     * @return Collection<int, Message>
     */
    public function execute(Chat $chat, int $parentUserMessageId): Collection
    {
        return $this->list->execute($chat, $parentUserMessageId);
    }
}
