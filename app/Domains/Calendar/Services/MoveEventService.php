<?php

namespace App\Domains\Calendar\Services;

use App\Domains\Calendar\Actions\FindEventAction;
use App\Domains\Calendar\Actions\UpdateEventAction;
use App\Domains\Calendar\Models\Event;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

final class MoveEventService
{
    public function __construct(
        private FindEventAction $find,
        private UpdateEventAction $update,
    ) {}

    /**
     * Drag-move: shift the event's start_at to $newStartAt and preserve its
     * original duration (end_at - start_at). For recurring events, this moves
     * the entire series anchor (per plan: v1 edits the whole series).
     */
    public function execute(User $actor, int $eventId, string $newStartAt): Event
    {
        $event = $this->find->execute($eventId);

        Gate::forUser($actor)->authorize('update', $event);

        $validated = Validator::make(['start_at' => $newStartAt], [
            'start_at' => ['required', 'date'],
        ])->validate();

        $newStart = CarbonImmutable::parse($validated['start_at']);
        $duration = $event->end_at->getTimestamp() - $event->start_at->getTimestamp();
        $newEnd = $newStart->addSeconds(max(0, $duration));

        return DB::transaction(fn () => $this->update->execute($event, [
            'start_at' => $newStart,
            'end_at' => $newEnd,
        ]));
    }
}
