<?php

namespace App\Domains\Preferences\Actions;

use App\Domains\Preferences\Models\UserPreference;
use Illuminate\Database\Eloquent\Collection;

class ListUserPreferencesAction
{
    /**
     * @return Collection<int, UserPreference>
     */
    public function execute(int $userId, ?string $keyPrefix = null): Collection
    {
        $query = UserPreference::query()->where('user_id', $userId);

        if ($keyPrefix !== null) {
            $query->where('key', 'like', $keyPrefix.'%');
        }

        return $query->orderBy('key')->get();
    }
}
