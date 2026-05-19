<?php

namespace App\Domains\Chat\Services;

use App\Domains\Chat\Actions\ListExpiredChatsAction;
use Illuminate\Support\Facades\Log;

final class PurgeExpiredChatsService
{
    public const DEFAULT_DAYS = 10;

    public function __construct(
        private ListExpiredChatsAction $listExpired,
        private DeleteChatService $deleteChat,
    ) {}

    public function execute(int $days = self::DEFAULT_DAYS): int
    {
        $cutoff = now()->subDays($days);
        $expiredChats = $this->listExpired->execute($cutoff);

        $deleted = 0;
        foreach ($expiredChats as $chat) {
            $this->deleteChat->delete($chat);
            $deleted++;
        }

        Log::info('Purged expired chats', ['days' => $days, 'count' => $deleted]);

        return $deleted;
    }
}
