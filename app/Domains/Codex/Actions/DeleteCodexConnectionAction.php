<?php

namespace App\Domains\Codex\Actions;

use App\Domains\Codex\Models\CodexConnection;

class DeleteCodexConnectionAction
{
    public function execute(CodexConnection $connection): void
    {
        $connection->delete();
    }
}
