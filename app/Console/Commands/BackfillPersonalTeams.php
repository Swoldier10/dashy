<?php

namespace App\Console\Commands;

use App\Domains\Teams\Services\EnsurePersonalTeamService;
use App\Models\User;
use Illuminate\Console\Command;

class BackfillPersonalTeams extends Command
{
    protected $signature = 'teams:backfill-personal';

    protected $description = 'Give every user without any team a personal team (idempotent).';

    public function handle(EnsurePersonalTeamService $ensurePersonalTeam): int
    {
        $users = User::doesntHave('teams')->get();

        if ($users->isEmpty()) {
            $this->info('All users already have at least one team.');

            return self::SUCCESS;
        }

        foreach ($users as $user) {
            $ensurePersonalTeam->execute($user);
            $this->line("Created personal team for user #{$user->id} ({$user->email}).");
        }

        $this->info(sprintf('Backfilled personal teams for %d user(s).', $users->count()));

        return self::SUCCESS;
    }
}
