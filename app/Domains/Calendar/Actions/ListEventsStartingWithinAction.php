<?php

namespace App\Domains\Calendar\Actions;

use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Models\Event;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

/**
 * Cross-user twin of ListEventsForUserInRangeAction for the notifications
 * scheduler: every series row (any owner) that could produce an occurrence
 * overlapping [$from, $to]. Expansion is the caller's job.
 */
class ListEventsStartingWithinAction
{
    /**
     * @return Collection<int, Event>
     */
    public function execute(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return Event::query()
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
