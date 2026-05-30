<?php

namespace App\Domains\Codex\Services;

use App\Domains\Codex\Actions\FindCodexConnectionForUserAction;
use App\Domains\Codex\Models\CodexConnection;
use App\Models\User;

/**
 * Returns the user's Codex (long-term memory) OAuth connection, or null if
 * none exists. No authorization needed — every user can only ever fetch
 * their own connection via this entry point.
 */
final class FindCodexConnectionForUserService
{
    public function __construct(
        private FindCodexConnectionForUserAction $find,
    ) {}

    public function execute(User $user): ?CodexConnection
    {
        return $this->find->execute($user);
    }
}
