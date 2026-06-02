<?php

namespace App\Domains\Notifications\Actions;

use App\Domains\Notifications\Models\Notification;

class MarkNotificationReadAction
{
    /**
     * Scoped by user_id: a user can only ever mark their own notification
     * (implicit single-owner authorization). Returns the number of rows
     * updated (0 when not found, not owned, or already read).
     */
    public function execute(int $userId, int $notificationId): int
    {
        return Notification::query()
            ->where('user_id', $userId)
            ->whereKey($notificationId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
