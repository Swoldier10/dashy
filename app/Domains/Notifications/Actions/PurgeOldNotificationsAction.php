<?php

namespace App\Domains\Notifications\Actions;

use App\Domains\Notifications\Models\Notification;
use Carbon\CarbonImmutable;

class PurgeOldNotificationsAction
{
    /**
     * Deletes read notifications older than $readBefore and any notification
     * older than $allBefore, regardless of read state.
     */
    public function execute(CarbonImmutable $readBefore, CarbonImmutable $allBefore): int
    {
        return Notification::query()
            ->where(function ($query) use ($readBefore, $allBefore) {
                $query
                    ->where(function ($read) use ($readBefore) {
                        $read->whereNotNull('read_at')->where('read_at', '<', $readBefore);
                    })
                    ->orWhere('created_at', '<', $allBefore);
            })
            ->delete();
    }
}
