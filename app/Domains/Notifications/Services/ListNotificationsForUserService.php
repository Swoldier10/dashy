<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Notifications\Actions\ListNotificationsForUserAction;
use App\Domains\Notifications\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class ListNotificationsForUserService
{
    public function __construct(
        private ListNotificationsForUserAction $list,
    ) {}

    /**
     * @return Collection<int, Notification>
     */
    public function execute(User $actor, int $limit = 30, bool $unreadOnly = false): Collection
    {
        return $this->list->execute($actor->id, min(max($limit, 1), 100), $unreadOnly);
    }
}
