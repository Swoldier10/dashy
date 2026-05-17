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
use Illuminate\Validation\ValidationException;

final class ResizeEventService
{
    private const MIN_DURATION_SECONDS = 15 * 60;

    public function __construct(
        private FindEventAction $find,
        private UpdateEventAction $update,
    ) {}

    /**
     * Drag-resize from the bottom edge: shift end_at to $newEndAt. Rejects
     * durations shorter than 15 minutes.
     */
    public function execute(User $actor, int $eventId, string $newEndAt): Event
    {
        $event = $this->find->execute($eventId);

        Gate::forUser($actor)->authorize('update', $event);

        $validated = Validator::make(['end_at' => $newEndAt], [
            'end_at' => ['required', 'date'],
        ])->validate();

        $newEnd = CarbonImmutable::parse($validated['end_at']);
        $duration = $newEnd->getTimestamp() - $event->start_at->getTimestamp();

        if ($duration < self::MIN_DURATION_SECONDS) {
            throw ValidationException::withMessages([
                'end_at' => __('Events must be at least 15 minutes long.'),
            ]);
        }

        return DB::transaction(fn () => $this->update->execute($event, [
            'end_at' => $newEnd,
        ]));
    }
}
