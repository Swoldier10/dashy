<?php

namespace App\Domains\Tasks\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Compact, mockup-style due-date label for a task row.
 *
 * Today / Tomorrow / Yesterday / Sun / Sun 9am / May 22 / 2027-03-04 / —
 *
 * Accepts both `Illuminate\Support\Carbon` (Eloquent's `datetime` cast) and
 * `Carbon\CarbonImmutable` via the shared `CarbonInterface` base.
 */
final class TaskDateFormatter
{
    public static function format(?CarbonInterface $date, ?CarbonInterface $now = null): string
    {
        if ($date === null) {
            return '—';
        }

        // Normalize to a mutable Carbon so we can freely call copy()/startOfDay().
        $date = Carbon::instance($date);
        $now = $now ? Carbon::instance($now) : Carbon::now();
        $today = $now->copy()->startOfDay();
        $target = $date->copy()->startOfDay();

        $hasTime = $date->format('H:i:s') !== '00:00:00';

        if ($target->equalTo($today)) {
            return __('Today');
        }
        if ($target->equalTo($today->copy()->addDay())) {
            return __('Tomorrow');
        }
        if ($target->equalTo($today->copy()->subDay())) {
            return __('Yesterday');
        }

        // Same Mon–Sun week as now
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();
        if ($target->between($weekStart, $weekEnd)) {
            $dow = $date->format('D'); // Mon, Tue, …

            return $hasTime ? $dow.' '.self::compactTime($date) : $dow;
        }

        if ($date->year === $now->year) {
            return $date->format('M j');
        }

        return $date->format('Y-m-d');
    }

    private static function compactTime(Carbon $date): string
    {
        // 9am / 9:30am / 1pm
        $minutes = (int) $date->format('i');
        $hour = $date->format('g'); // 1-12 no leading zero
        $suffix = strtolower($date->format('a')); // am/pm

        return $minutes === 0 ? $hour.$suffix : $hour.':'.$date->format('i').$suffix;
    }
}
