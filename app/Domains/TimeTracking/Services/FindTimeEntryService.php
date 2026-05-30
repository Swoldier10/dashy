<?php

namespace App\Domains\TimeTracking\Services;

use App\Domains\TimeTracking\Actions\FindTimeEntryAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * Resolves a time entry by ID for an actor that's about to update or delete
 * it. Authorizes 'update' rather than 'view' so unauthorised callers fail
 * here instead of later inside Update/Delete services.
 */
final class FindTimeEntryService
{
    public function __construct(
        private FindTimeEntryAction $find,
    ) {}

    public function execute(User $actor, int $entryId): TimeEntry
    {
        $entry = $this->find->execute($entryId);

        Gate::forUser($actor)->authorize('update', $entry);

        return $entry;
    }
}
