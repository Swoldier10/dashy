<?php

namespace App\Domains\Codex\Actions;

use App\Domains\Codex\Models\CodexConnection;

class UpdateCodexConnectionAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(CodexConnection $connection, array $attributes): CodexConnection
    {
        $connection->forceFill($attributes)->save();

        return $connection->refresh();
    }
}
