<?php

namespace App\Domains\Calendar\Services;

use App\Domains\Calendar\Actions\CreateEventAction;
use App\Domains\Calendar\Enums\EventColor;
use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class CreateEventService
{
    public function __construct(
        private CreateEventAction $create,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes  title, description?, start_at, end_at,
     *                                            is_all_day?, color?, location?,
     *                                            recurrence_freq?, recurrence_until?
     */
    public function execute(User $actor, array $attributes): Event
    {
        Gate::forUser($actor)->authorize('create', Event::class);

        $validated = Validator::make($attributes, [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
            'is_all_day' => ['nullable', 'boolean'],
            'color' => ['nullable', Rule::enum(EventColor::class)],
            'location' => ['nullable', 'string', 'max:200'],
            'recurrence_freq' => ['nullable', Rule::enum(RecurrenceFreq::class)],
            'recurrence_until' => ['nullable', 'date', 'after_or_equal:start_at'],
        ])->validate();

        return DB::transaction(fn () => $this->create->execute([
            'user_id' => $actor->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_at' => $validated['start_at'],
            'end_at' => $validated['end_at'],
            'is_all_day' => $validated['is_all_day'] ?? false,
            'color' => $validated['color'] ?? EventColor::Danube->value,
            'location' => $validated['location'] ?? null,
            'recurrence_freq' => $validated['recurrence_freq'] ?? RecurrenceFreq::None->value,
            'recurrence_until' => $validated['recurrence_until'] ?? null,
        ]));
    }
}
