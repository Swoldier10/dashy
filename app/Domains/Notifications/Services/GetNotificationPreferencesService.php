<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Notifications\Enums\NotificationChannel;
use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Preferences\Services\GetUserPreferenceService;

final class GetNotificationPreferencesService
{
    public const PREFERENCE_KEY = 'notifications';

    public function __construct(
        private GetUserPreferenceService $getPreference,
    ) {}

    /**
     * Returns the complete channel map for every notification type: stored
     * choices merged over the enum defaults, so callers never need their own
     * default handling. Unknown or malformed stored entries are ignored.
     *
     * @return array<string, array{email: bool, app: bool}>
     */
    public function execute(int $userId): array
    {
        $stored = $this->getPreference->execute($userId, self::PREFERENCE_KEY);
        $stored = is_array($stored) ? $stored : [];

        $preferences = [];

        foreach (NotificationType::cases() as $type) {
            $channels = $type->defaultChannels();
            $override = $stored[$type->value] ?? null;

            if (is_array($override)) {
                foreach (NotificationChannel::cases() as $channel) {
                    if (is_bool($override[$channel->value] ?? null)) {
                        $channels[$channel->value] = $override[$channel->value];
                    }
                }
            }

            $preferences[$type->value] = $channels;
        }

        return $preferences;
    }
}
