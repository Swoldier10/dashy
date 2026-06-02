<?php

namespace App\Domains\Teams\Events;

use App\Domains\Teams\Models\Team;
use App\Models\User;

final class TeamMemberRemoved extends TeamEvent
{
    public function __construct(
        int $teamId,
        string $teamName,
        public int $removedUserId,
        public ?int $actorUserId,
        public string $actorName,
        public bool $wasSelfLeave,
    ) {
        parent::__construct($teamId, $teamName);
    }

    public static function fromTeam(Team $team, User $removed, User $actor): self
    {
        return new self(
            $team->id,
            (string) $team->name,
            $removed->id,
            $actor->id,
            $actor->name,
            $actor->is($removed),
        );
    }
}
