<?php

namespace App\Domains\Calendar\Actions;

use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Models\Event;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

class ListEventsForUserInRangeAction
{
    /**
     * Returns every series row that *could* produce an occurrence in [$from, $to]:
     *   - non-recurring events whose [start_at, end_at] overlaps [from, to]
     *   - recurring events whose start_at <= to and (recurrence_until is null
     *     or recurrence_until >= from::date)
     *
     * Expansion into virtual occurrences is the caller's job
     * (see ExpandEventOccurrencesAction).
     *
     * @return Collection<int, Event>
     */
    public function execute(User $user, CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return Event::query()
            ->where('user_id', $user->id)
            ->where(function ($q) use ($from, $to) {
                $q->where(function ($q) use ($from, $to) {
                    $q->where('recurrence_freq', RecurrenceFreq::None->value)
                        ->where('start_at', '<=', $to)
                        ->where('end_at', '>=', $from);
                })->orWhere(function ($q) use ($from, $to) {
                    $q->where('recurrence_freq', '!=', RecurrenceFreq::None->value)
                        ->where('start_at', '<=', $to)
                        ->where(function ($q) use ($from) {
                            $q->whereNull('recurrence_until')
                                ->orWhereDate('recurrence_until', '>=', $from->toDateString());
                        });
                });
            })
            ->orderBy('start_at')
            ->orderBy('id')
            ->get();
    }
}
