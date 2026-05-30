<?php

namespace App\Domains\Teams\Services;

use App\Domains\Teams\Actions\DeleteExpiredInvitationsAction;
use Illuminate\Support\Facades\DB;

/**
 * Deletes team invitations that have expired without ever being accepted or
 * revoked. Run on a daily schedule so stale pending invitations don't
 * accumulate. Returns the number of invitations removed.
 */
final class PurgeExpiredInvitationsService
{
    public function __construct(
        private DeleteExpiredInvitationsAction $deleteExpired,
    ) {}

    public function execute(): int
    {
        return DB::transaction(fn (): int => $this->deleteExpired->execute());
    }
}
