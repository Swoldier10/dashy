<?php

namespace App\Domains\Notifications\Actions;

use App\Domains\Notifications\DTOs\NotificationPayload;
use App\Domains\Notifications\Models\Notification;
use App\Support\Concerns\DetectsUniqueConstraintViolations;
use Illuminate\Database\QueryException;

class CreateNotificationAction
{
    use DetectsUniqueConstraintViolations;

    /**
     * Persist one notification row. Returns null when the payload carries a
     * dedupe key that already exists (insert-or-ignore), so overlapping
     * scheduler runs never double-notify. $asRead stores the row pre-read —
     * used when it only serves as the reminder dedupe ledger and must not
     * light the unread badge.
     */
    public function execute(NotificationPayload $payload, bool $asRead = false): ?Notification
    {
        try {
            return Notification::create([
                'user_id' => $payload->recipientUserId,
                'team_id' => $payload->teamId,
                'actor_user_id' => $payload->actorUserId,
                'type' => $payload->type->value,
                'subject_type' => $payload->subjectType,
                'subject_id' => $payload->subjectId,
                'data' => $payload->data,
                'dedupe_key' => $payload->dedupeKey,
                'read_at' => $asRead ? now() : null,
            ]);
        } catch (QueryException $e) {
            if ($payload->dedupeKey !== null && $this->isUniqueViolation($e)) {
                return null;
            }

            throw $e;
        }
    }
}
