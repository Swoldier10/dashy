<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Notifications\Actions\MarkNotificationReadAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class MarkNotificationReadService
{
    public function __construct(
        private MarkNotificationReadAction $markRead,
    ) {}

    /**
     * The action is scoped to the acting user, so a user can only ever mark
     * their own notifications (implicit single-owner authorization).
     */
    public function execute(User $actor, int $notificationId): int
    {
        return DB::transaction(fn () => $this->markRead->execute($actor->id, $notificationId));
    }
}
