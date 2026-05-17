<?php

namespace App\Domains\Codex\Actions;

use App\Domains\Codex\Models\CodexConnection;

class CreateCodexConnectionAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): CodexConnection
    {
        $connection = new CodexConnection;
        $connection->forceFill($attributes);
        $connection->save();

        return $connection->refresh();
    }
}
