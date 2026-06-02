<?php

namespace App\Domains\Notifications\DTOs;

use App\Domains\Notifications\Enums\NotificationType;

final readonly class NotificationPayload
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public NotificationType $type,
        public int $recipientUserId,
        public ?int $actorUserId = null,
        public ?int $teamId = null,
        public ?string $subjectType = null,
        public ?int $subjectId = null,
        public array $data = [],
        public ?string $dedupeKey = null,
    ) {}
}
