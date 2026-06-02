<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Notifications\Actions\MarkAllNotificationsReadForUserAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class MarkAllNotificationsReadService
{
    public function __construct(
        private MarkAllNotificationsReadForUserAction $markAllRead,
    ) {}

    public function execute(User $actor): int
    {
        return DB::transaction(fn () => $this->markAllRead->execute($actor->id));
    }
}
