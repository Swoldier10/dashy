<?php

namespace App\Domains\Chat\Services;

use App\Domains\Chat\Actions\ListUserChatsAction;
use App\Domains\Chat\Models\Chat;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class ListRecentChatsService
{
    public function __construct(
        private ListUserChatsAction $listChats,
    ) {}

    /**
     * Most recent chats for the actor, newest first. `limit` caps the result so
     * the AI chat can't accidentally pull a huge history; the caller can ask
     * for up to 50.
     *
     * @return Collection<int, Chat>
     */
    public function execute(User $actor, int $limit = 10): Collection
    {
        $limit = max(1, min(50, $limit));

        return $this->listChats->execute($actor)->take($limit);
    }
}
