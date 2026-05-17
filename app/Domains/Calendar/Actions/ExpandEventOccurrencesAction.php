<?php

namespace App\Domains\Calendar\Actions;

use App\Domains\Calendar\DTOs\EventOccurrence;
use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Models\Event;
use Carbon\CarbonImmutable;

class ExpandEventOccurrencesAction
{
    /**
     * Expand a single event row into the list of occurrences that overlap
     * the [$from, $to] window. Pure function — no DB access.
     *
     * Rules:
     *   - none: yields one occurrence at start_at..end_at (when it overlaps)
     *   - daily/weekly: anchor + N days / weeks
     *   - monthly/yearly: anchor + N months / years, **skipping** indices whose
     *     target month does not contain the anchor day (e.g. monthly-on-31st
     *     skips April; yearly-on-Feb-29 skips non-leap years). Matches
     *     Google Calendar's default for "impossible" dates.
     *   - recurrence_until: inclusive upper bound on the occurrence date.
     *   - safety cap: walk at most 5,000 indices so an unbounded series with
     *     a huge query window still terminates predictably.
     *
     * @return list<EventOccurrence>
     */
    public function execute(Event $event, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $freq = $event->recurrence_freq instanceof RecurrenceFreq
            ? $event->recurrence_freq
            : RecurrenceFreq::from((string) $event->recurrence_freq);

        $start = CarbonImmutable::parse($event->start_at);
        $end = CarbonImmutable::parse($event->end_at);
        $duration = $end->getTimestamp() - $start->getTimestamp();

        if ($freq === RecurrenceFreq::None) {
            if ($start->lte($to) && $end->gte($from)) {
                return [new EventOccurrence($event, $start, $end)];
            }

            return [];
        }

        $until = $event->recurrence_until
            ? CarbonImmutable::parse($event->recurrence_until)->endOfDay()
            : null;

        $effectiveTo = $until && $until->lt($to) ? $until : $to;

        $out = [];
        $maxSteps = 5000;

        for ($index = 0; $index < $maxSteps; $index++) {
            $candidate = $this->candidateAt($start, $freq, $index);

            if ($candidate === null) {
                // Skip impossible-date indices for monthly/yearly.
                continue;
            }

            if ($candidate->gt($effectiveTo)) {
                break;
            }

            $candidateEnd = $candidate->addSeconds($duration);

            if ($candidateEnd->gte($from)) {
                $out[] = new EventOccurrence($event, $candidate, $candidateEnd);
            }
        }

        return $out;
    }

    private function candidateAt(CarbonImmutable $start, RecurrenceFreq $freq, int $index): ?CarbonImmutable
    {
        return match ($freq) {
            RecurrenceFreq::Daily => $start->addDays($index),
            RecurrenceFreq::Weekly => $start->addWeeks($index),
            RecurrenceFreq::Monthly => $this->monthlyCandidate($start, $index),
            RecurrenceFreq::Yearly => $this->yearlyCandidate($start, $index),
            RecurrenceFreq::None => $index === 0 ? $start : null,
        };
    }

    /**
     * Returns the candidate $index months after $start, or null when the
     * target month doesn't have the anchor day (e.g. anchor=31, target=April).
     */
    private function monthlyCandidate(CarbonImmutable $start, int $index): ?CarbonImmutable
    {
        $anchorDay = (int) $start->format('j');
        // addMonthsNoOverflow clamps to the last valid day, which lets us read
        // the target month's day-count without tripping a date overflow.
        $clamped = $start->addMonthsNoOverflow($index);
        $daysInMonth = (int) $clamped->endOfMonth()->format('j');

        if ($anchorDay > $daysInMonth) {
            return null;
        }

        return $clamped->setDate(
            (int) $clamped->format('Y'),
            (int) $clamped->format('n'),
            $anchorDay,
        )->setTime(
            (int) $start->format('G'),
            (int) $start->format('i'),
            (int) $start->format('s'),
        );
    }

    /**
     * Returns the candidate $index years after $start, or null when the
     * target year's anchor month doesn't have the anchor day (Feb 29 in
     * non-leap years).
     */
    private function yearlyCandidate(CarbonImmutable $start, int $index): ?CarbonImmutable
    {
        $anchorDay = (int) $start->format('j');
        $anchorMonth = (int) $start->format('n');
        $year = (int) $start->format('Y') + $index;

        $daysInMonth = (int) CarbonImmutable::create($year, $anchorMonth, 1)
            ->endOfMonth()
            ->format('j');

        if ($anchorDay > $daysInMonth) {
            return null;
        }

        return $start
            ->setDate($year, $anchorMonth, $anchorDay)
            ->setTime(
                (int) $start->format('G'),
                (int) $start->format('i'),
                (int) $start->format('s'),
            );
    }
}
