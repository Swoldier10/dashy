<?php

namespace App\Domains\Preferences\Actions;

use App\Domains\Preferences\Models\UserPreference;

class DeleteUserPreferenceAction
{
    public function execute(int $userId, string $key): int
    {
        return UserPreference::query()
            ->where('user_id', $userId)
            ->where('key', $key)
            ->delete();
    }
}
