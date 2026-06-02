<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Notifications\Enums\NotificationChannel;
use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Preferences\Services\SetUserPreferenceService;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class UpdateNotificationPreferencesService
{
    public function __construct(
        private SetUserPreferenceService $setPreference,
    ) {}

    /**
     * Validates and stores the full per-type channel map. Unknown types or
     * non-boolean channel values are rejected with keyed errors.
     *
     * @param  array<string, mixed>  $preferences
     * @return array<string, array{email: bool, app: bool}>
     */
    public function execute(User $user, array $preferences): array
    {
        /** @var array<string, array<int, string>> $errors */
        $errors = [];
        /** @var array<string, array{email: bool, app: bool}> $normalized */
        $normalized = [];

        foreach ($preferences as $typeValue => $channels) {
            if (NotificationType::tryFrom((string) $typeValue) === null) {
                $errors['preferences'][] = __('Unknown notification type.');

                continue;
            }

            if (! is_array($channels)) {
                $errors["preferences.$typeValue"][] = __('Channels must be provided per notification type.');

                continue;
            }

            foreach (NotificationChannel::cases() as $channel) {
                if (! is_bool($channels[$channel->value] ?? null)) {
                    $errors["preferences.$typeValue.{$channel->value}"][] = __('Channel values must be true or false.');
                }
            }

            if (! isset($errors["preferences.$typeValue.email"]) && ! isset($errors["preferences.$typeValue.app"])) {
                $normalized[(string) $typeValue] = [
                    'email' => $channels['email'],
                    'app' => $channels['app'],
                ];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        $this->setPreference->execute(
            $user->id,
            GetNotificationPreferencesService::PREFERENCE_KEY,
            $normalized,
        );

        return $normalized;
    }
}
