<?php

namespace App\Domains\Calendar\Services;

use App\Domains\Calendar\Actions\ExpandEventOccurrencesAction;
use App\Domains\Calendar\Actions\ListEventsForUserInRangeAction;
use App\Domains\Calendar\DTOs\EventOccurrence;
use App\Models\User;
use Carbon\CarbonImmutable;

final class ListEventsForUserInRangeService
{
    public function __construct(
        private ListEventsForUserInRangeAction $list,
        private ExpandEventOccurrencesAction $expand,
    ) {}

    /**
     * Returns every occurrence (concrete + virtual from recurring series)
     * overlapping [$from, $to], sorted by start.
     *
     * @return list<EventOccurrence>
     */
    public function execute(User $actor, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $series = $this->list->execute($actor, $from, $to);

        $occurrences = [];

        foreach ($series as $event) {
            foreach ($this->expand->execute($event, $from, $to) as $occ) {
                $occurrences[] = $occ;
            }
        }

        usort($occurrences, function (EventOccurrence $a, EventOccurrence $b): int {
            return $a->startAt->getTimestamp() <=> $b->startAt->getTimestamp()
                ?: $a->event->id <=> $b->event->id;
        });

        return $occurrences;
    }
}
