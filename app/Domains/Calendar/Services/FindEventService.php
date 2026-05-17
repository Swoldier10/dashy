<?php

namespace App\Domains\Calendar\Services;

use App\Domains\Calendar\Actions\FindEventAction;
use App\Domains\Calendar\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class FindEventService
{
    public function __construct(
        private FindEventAction $find,
    ) {}

    public function execute(User $actor, int $eventId): Event
    {
        $event = $this->find->execute($eventId);

        Gate::forUser($actor)->authorize('view', $event);

        return $event;
    }
}
