<?php

namespace App\Domains\Teams\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Base for team membership notification events. Emitted by the Teams domain
 * after commit; the Notifications domain listens — Teams knows nothing about
 * Notifications.
 */
abstract class TeamEvent
{
    use Dispatchable;

    public function __construct(
        public int $teamId,
        public string $teamName,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function baseData(): array
    {
        return [
            'team_id' => $this->teamId,
            'team_name' => $this->teamName,
        ];
    }
}
