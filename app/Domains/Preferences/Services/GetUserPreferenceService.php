<?php

namespace App\Domains\Preferences\Services;

use App\Domains\Preferences\Actions\FindUserPreferenceAction;

/**
 * Generic public read for a single user preference value. Other domains that
 * store their settings in the Preferences key/JSON store consume this instead
 * of reaching into the Preferences actions.
 */
final class GetUserPreferenceService
{
    public function __construct(
        private FindUserPreferenceAction $find,
    ) {}

    public function execute(int $userId, string $key): mixed
    {
        return $this->find->execute($userId, $key)?->value;
    }
}
