<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Notifications\Actions\PurgeOldNotificationsAction;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class PurgeOldNotificationsService
{
    public const READ_RETENTION_DAYS = 30;

    public const HARD_RETENTION_DAYS = 90;

    public function __construct(
        private PurgeOldNotificationsAction $purge,
    ) {}

    public function execute(): int
    {
        $now = CarbonImmutable::now();

        return DB::transaction(fn () => $this->purge->execute(
            $now->subDays(self::READ_RETENTION_DAYS),
            $now->subDays(self::HARD_RETENTION_DAYS),
        ));
    }
}
