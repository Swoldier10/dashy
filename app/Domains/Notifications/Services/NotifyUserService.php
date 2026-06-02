<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Auth\Services\FindUserByIdService;
use App\Domains\Notifications\Actions\CreateNotificationAction;
use App\Domains\Notifications\DTOs\NotificationPayload;
use App\Domains\Notifications\Mail\NotificationMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Central delivery for every notification: suppresses self-notifications,
 * gates each channel on the recipient's preferences, dedupes scheduler
 * reminders, persists the in-app row, and queues the e-mail.
 */
final class NotifyUserService
{
    public function __construct(
        private FindUserByIdService $findUser,
        private GetNotificationPreferencesService $getPreferences,
        private CreateNotificationAction $create,
    ) {}

    public function execute(NotificationPayload $payload): void
    {
        if ($payload->actorUserId !== null && $payload->actorUserId === $payload->recipientUserId) {
            return;
        }

        $recipient = $this->findUser->execute($payload->recipientUserId);

        if (! $recipient instanceof User) {
            return;
        }

        $channels = $this->getPreferences->execute($recipient->id)[$payload->type->value];

        if (! $channels['app'] && ! $channels['email']) {
            return;
        }

        // Deduped (reminder) payloads always persist a row — it is the
        // idempotency ledger. With the app channel off it is stored pre-read
        // so it never lights the badge but still blocks repeat e-mails.
        if ($channels['app'] || $payload->dedupeKey !== null) {
            $created = DB::transaction(fn () => $this->create->execute($payload, asRead: ! $channels['app']));

            if ($created === null) {
                return;
            }
        }

        if ($channels['email']) {
            Mail::to($recipient)->queue(new NotificationMail(
                $payload->type,
                $payload->data,
                $recipient->first_name ?: $recipient->name,
            ));
        }
    }
}
