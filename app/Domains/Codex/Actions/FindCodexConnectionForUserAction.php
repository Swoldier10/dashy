<?php

namespace App\Domains\Codex\Actions;

use App\Domains\Codex\Models\CodexConnection;
use App\Models\User;

class FindCodexConnectionForUserAction
{
    public function execute(User $user): ?CodexConnection
    {
        return CodexConnection::where('user_id', $user->id)->first();
    }
}
