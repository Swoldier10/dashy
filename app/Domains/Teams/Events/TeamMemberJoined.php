<?php

namespace App\Domains\Teams\Events;

use App\Domains\Teams\Models\Team;
use App\Models\User;

final class TeamMemberJoined extends TeamEvent
{
    /**
     * @param  list<int>  $otherMemberIds
     */
    public function __construct(
        int $teamId,
        string $teamName,
        public int $joinedUserId,
        public string $joinedUserName,
        public array $otherMemberIds,
        public ?int $invitedByUserId = null,
    ) {
        parent::__construct($teamId, $teamName);
    }

    /**
     * @param  list<int>  $otherMemberIds
     */
    public static function fromTeam(Team $team, User $joined, array $otherMemberIds, ?int $invitedByUserId = null): self
    {
        return new self($team->id, (string) $team->name, $joined->id, $joined->name, $otherMemberIds, $invitedByUserId);
    }
}
