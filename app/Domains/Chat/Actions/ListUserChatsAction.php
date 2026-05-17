<?php

namespace App\Domains\Chat\Actions;

use App\Domains\Chat\Models\Chat;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListUserChatsAction
{
    /**
     * @return Collection<int, Chat>
     */
    public function execute(User $user): Collection
    {
        return Chat::where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->get(['id', 'title', 'updated_at']);
    }
}
