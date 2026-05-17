<?php

namespace App\Domains\Preferences\Actions;

use App\Domains\Preferences\Models\UserPreference;

class UpsertUserPreferenceAction
{
    /**
     * Insert-or-update by (user_id, key). The value is stored as JSON so any
     * scalar/array structure is preserved verbatim.
     */
    public function execute(int $userId, string $key, mixed $value): UserPreference
    {
        $pref = UserPreference::query()
            ->where('user_id', $userId)
            ->where('key', $key)
            ->first();

        if ($pref !== null) {
            $pref->forceFill(['value' => $value])->save();

            return $pref;
        }

        return UserPreference::create([
            'user_id' => $userId,
            'key' => $key,
            'value' => $value,
        ]);
    }
}
