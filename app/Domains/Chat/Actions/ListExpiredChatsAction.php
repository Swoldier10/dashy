<?php

namespace App\Domains\Chat\Actions;

use App\Domains\Chat\Models\Chat;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

class ListExpiredChatsAction
{
    /**
     * @return Collection<int, Chat>
     */
    public function execute(CarbonInterface $cutoff): Collection
    {
        return Chat::query()
            ->where('updated_at', '<', $cutoff)
            ->orderBy('id')
            ->get();
    }
}
