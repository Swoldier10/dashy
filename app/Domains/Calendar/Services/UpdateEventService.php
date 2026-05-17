<?php

namespace App\Domains\Calendar\Services;

use App\Domains\Calendar\Actions\FindEventAction;
use App\Domains\Calendar\Actions\UpdateEventAction;
use App\Domains\Calendar\Enums\EventColor;
use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class UpdateEventService
{
    public function __construct(
        private FindEventAction $find,
        private UpdateEventAction $update,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes  any subset of: title, description,
     *                                            start_at, end_at, is_all_day,
     *                                            color, location, recurrence_freq,
     *                                            recurrence_until
     */
    public function execute(User $actor, int $eventId, array $attributes): Event
    {
        $event = $this->find->execute($eventId);

        Gate::forUser($actor)->authorize('update', $event);

        $merged = array_merge([
            'start_at' => $event->start_at?->format('Y-m-d H:i:s'),
            'end_at' => $event->end_at?->format('Y-m-d H:i:s'),
        ], $attributes);

        $validated = Validator::make($merged, [
            'title' => ['sometimes', 'required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'start_at' => ['sometimes', 'required', 'date'],
            'end_at' => ['sometimes', 'required', 'date', 'after_or_equal:start_at'],
            'is_all_day' => ['sometimes', 'boolean'],
            'color' => ['sometimes', Rule::enum(EventColor::class)],
            'location' => ['nullable', 'string', 'max:200'],
            'recurrence_freq' => ['sometimes', Rule::enum(RecurrenceFreq::class)],
            'recurrence_until' => ['nullable', 'date', 'after_or_equal:start_at'],
        ])->validate();

        $payload = array_intersect_key($validated, array_flip([
            'title', 'description', 'start_at', 'end_at',
            'is_all_day', 'color', 'location',
            'recurrence_freq', 'recurrence_until',
        ]));

        return DB::transaction(fn () => $this->update->execute($event, $payload));
    }
}
