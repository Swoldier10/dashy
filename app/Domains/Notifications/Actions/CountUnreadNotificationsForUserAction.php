<?php

namespace App\Domains\Notifications\Actions;

use App\Domains\Notifications\Models\Notification;

class CountUnreadNotificationsForUserAction
{
    public function execute(int $userId): int
    {
        return Notification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }
}
