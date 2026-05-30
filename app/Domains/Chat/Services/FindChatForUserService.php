<?php

namespace App\Domains\Chat\Services;

use App\Domains\Chat\Actions\FindChatForUserAction;
use App\Domains\Chat\Models\Chat;
use App\Models\User;

/**
 * Returns the chat only when it belongs to the user. Returns null when no
 * match exists — callers branch on null (the underlying action already
 * scopes by user_id, so no extra authorization step is needed).
 */
final class FindChatForUserService
{
    public function __construct(
        private FindChatForUserAction $find,
    ) {}

    public function execute(User $user, int $chatId): ?Chat
    {
        return $this->find->execute($user, $chatId);
    }
}
