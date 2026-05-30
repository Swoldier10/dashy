<?php

namespace App\Domains\Chat\Services;

use App\Domains\Chat\Actions\ListUserChatsAction;
use App\Domains\Chat\Models\Chat;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Full list of a user's chats, newest first. Used by the sidebar to render
 * the chat history list. Use ListRecentChatsService when a fixed cap is
 * preferable (the AI surface).
 */
final class ListUserChatsService
{
    public function __construct(
        private ListUserChatsAction $list,
    ) {}

    /**
     * @return Collection<int, Chat>
     */
    public function execute(User $user): Collection
    {
        return $this->list->execute($user);
    }
}
