<?php

namespace App\Domains\TimeTracking\Services;

use App\Domains\TimeTracking\Actions\DeleteTimeEntryAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class DeleteTimeEntryService
{
    public function __construct(
        private DeleteTimeEntryAction $delete,
    ) {}

    public function execute(User $actor, TimeEntry $entry): void
    {
        Gate::forUser($actor)->authorize('delete', $entry);

        DB::transaction(fn () => $this->delete->execute($entry));
    }
}
