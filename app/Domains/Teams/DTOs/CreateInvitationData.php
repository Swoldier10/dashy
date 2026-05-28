<?php

namespace App\Domains\Teams\DTOs;

use App\Domains\Teams\Enums\TeamRole;
use Carbon\CarbonImmutable;

final readonly class CreateInvitationData
{
    public function __construct(
        public int $teamId,
        public string $email,
        public TeamRole $role,
        public string $tokenHash,
        public CarbonImmutable $expiresAt,
        public ?int $invitedByUserId,
        public CarbonImmutable $lastSentAt,
    ) {}
}
