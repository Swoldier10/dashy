<?php

namespace App\Domains\Codex\Services;

use App\Domains\Codex\Actions\DeleteCodexConnectionAction;
use App\Domains\Codex\Actions\FindCodexConnectionForUserAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DisconnectCodexService
{
    public function __construct(
        private FindCodexConnectionForUserAction $find,
        private DeleteCodexConnectionAction $delete,
    ) {}

    public function execute(User $user): void
    {
        $connection = $this->find->execute($user);
        if ($connection === null) {
            return;
        }

        DB::transaction(fn () => $this->delete->execute($connection));
    }
}
