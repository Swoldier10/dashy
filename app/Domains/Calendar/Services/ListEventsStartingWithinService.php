<?php

namespace App\Domains\Calendar\Services;

use App\Domains\Calendar\Actions\ExpandEventOccurrencesAction;
use App\Domains\Calendar\Actions\ListEventsStartingWithinAction;
use App\Domains\Calendar\DTOs\EventOccurrence;
use Carbon\CarbonImmutable;

/**
 * Cross-user read for the notifications scheduler — returns every occurrence
 * (concrete + recurring, any owner) whose START falls inside [$from, $to].
 * Occurrence starts are anchored to the series start, so they are stable
 * across scheduler ticks (required for reminder dedupe keys). Only safe for
 * internal callers (console commands, queued jobs).
 */
final class ListEventsStartingWithinService
{
    public function __construct(
        private ListEventsStartingWithinAction $list,
        private ExpandEventOccurrencesAction $expand,
    ) {}

    /**
     * @return list<EventOccurrence>
     */
    public function execute(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $occurrences = [];

        foreach ($this->list->execute($from, $to) as $event) {
            foreach ($this->expand->execute($event, $from, $to) as $occurrence) {
                if ($occurrence->startAt->between($from, $to)) {
                    $occurrences[] = $occurrence;
                }
            }
        }

        return $occurrences;
    }
}
