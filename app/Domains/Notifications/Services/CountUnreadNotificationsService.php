<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Notifications\Actions\CountUnreadNotificationsForUserAction;
use App\Models\User;

final class CountUnreadNotificationsService
{
    public function __construct(
        private CountUnreadNotificationsForUserAction $count,
    ) {}

    public function execute(User $actor): int
    {
        return $this->count->execute($actor->id);
    }
}
