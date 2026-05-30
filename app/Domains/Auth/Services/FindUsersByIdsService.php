<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Actions\FindUsersByIdsAction;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Bulk read of users by ID, returning a slim (id, name) collection. Used by
 * AiToolCardPresenter to render `@mention`-style chips for stored tool-call
 * payloads; the caller decides what's safe to display.
 */
final class FindUsersByIdsService
{
    public function __construct(
        private FindUsersByIdsAction $find,
    ) {}

    /**
     * @param  array<int, int>  $ids
     * @return Collection<int, User>
     */
    public function execute(array $ids): Collection
    {
        return $this->find->execute($ids);
    }
}
