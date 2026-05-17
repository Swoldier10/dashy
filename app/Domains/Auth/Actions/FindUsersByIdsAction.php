<?php

namespace App\Domains\Auth\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class FindUsersByIdsAction
{
    /**
     * @param  array<int, int>  $ids
     * @return Collection<int, User>
     */
    public function execute(array $ids): Collection
    {
        if ($ids === []) {
            return new Collection;
        }

        return User::query()->whereIn('id', $ids)->get(['id', 'name']);
    }
}
