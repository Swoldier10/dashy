<?php

namespace App\Domains\Calendar\Services;

use App\Domains\Calendar\Actions\DeleteEventAction;
use App\Domains\Calendar\Actions\FindEventAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class DeleteEventService
{
    public function __construct(
        private FindEventAction $find,
        private DeleteEventAction $delete,
    ) {}

    public function execute(User $actor, int $eventId): void
    {
        $event = $this->find->execute($eventId);

        Gate::forUser($actor)->authorize('delete', $event);

        DB::transaction(fn () => $this->delete->execute($event));
    }
}
