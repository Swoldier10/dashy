<?php

namespace App\Domains\Teams\Events;

use App\Domains\Teams\Models\Team;
use App\Models\User;

final class TeamInvitationAccepted extends TeamEvent
{
    public function __construct(
        int $teamId,
        string $teamName,
        public ?int $invitedByUserId,
        public int $acceptedByUserId,
        public string $acceptedByName,
    ) {
        parent::__construct($teamId, $teamName);
    }

    public static function fromTeam(Team $team, ?int $invitedByUserId, User $acceptedBy): self
    {
        return new self($team->id, (string) $team->name, $invitedByUserId, $acceptedBy->id, $acceptedBy->name);
    }
}
