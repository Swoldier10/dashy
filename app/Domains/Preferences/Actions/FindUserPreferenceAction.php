<?php

namespace App\Domains\Preferences\Actions;

use App\Domains\Preferences\Models\UserPreference;

class FindUserPreferenceAction
{
    public function execute(int $userId, string $key): ?UserPreference
    {
        return UserPreference::query()
            ->where('user_id', $userId)
            ->where('key', $key)
            ->first();
    }
}
