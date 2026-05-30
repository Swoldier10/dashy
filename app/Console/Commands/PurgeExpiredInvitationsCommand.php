<?php

namespace App\Console\Commands;

use App\Domains\Teams\Services\PurgeExpiredInvitationsService;
use Illuminate\Console\Command;

class PurgeExpiredInvitationsCommand extends Command
{
    protected $signature = 'teams:purge-expired-invitations';

    protected $description = 'Delete team invitations that expired without being accepted or revoked.';

    public function handle(PurgeExpiredInvitationsService $service): int
    {
        $count = $service->execute();
        $this->info(sprintf('Purged %d expired invitation(s).', $count));

        return self::SUCCESS;
    }
}
