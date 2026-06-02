<?php

namespace App\Domains\Notifications\Actions;

use App\Domains\Notifications\Models\Notification;
use Illuminate\Database\Eloquent\Collection;

class ListNotificationsForUserAction
{
    /**
     * @return Collection<int, Notification>
     */
    public function execute(int $userId, int $limit = 30, bool $unreadOnly = false): Collection
    {
        return Notification::query()
            ->where('user_id', $userId)
            ->when($unreadOnly, fn ($query) => $query->whereNull('read_at'))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }
}
