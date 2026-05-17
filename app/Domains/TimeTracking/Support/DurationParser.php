<?php

namespace App\Domains\TimeTracking\Support;

use Illuminate\Validation\ValidationException;

final class DurationParser
{
    private const MAX_SECONDS = 86400 * 7;

    public static function parse(string $input): int
    {
        $value = trim(strtolower($input));
        if ($value === '') {
            throw self::invalid($input);
        }

        if (preg_match('/^\d+:[0-5]?\d(?::[0-5]?\d)?$/', $value)) {
            $parts = array_map('intval', explode(':', $value));
            $seconds = match (count($parts)) {
                2 => $parts[0] * 3600 + $parts[1] * 60,
                3 => $parts[0] * 3600 + $parts[1] * 60 + $parts[2],
                default => throw self::invalid($input),
            };

            return self::guard($seconds, $input);
        }

        if (preg_match('/^\d+(?:\.\d+)?$/', $value)) {
            $seconds = (int) round(((float) $value) * 60);

            return self::guard($seconds, $input);
        }

        $pattern = '/^(?:(\d+(?:\.\d+)?)\s*h)?\s*(?:(\d+(?:\.\d+)?)\s*m)?\s*(?:(\d+)\s*s)?$/';
        if (! preg_match($pattern, $value, $matches)) {
            throw self::invalid($input);
        }

        $hours = $matches[1] ?? '';
        $minutes = $matches[2] ?? '';
        $secondsPart = $matches[3] ?? '';

        if ($hours === '' && $minutes === '' && $secondsPart === '') {
            throw self::invalid($input);
        }

        $seconds = 0;
        if ($hours !== '') {
            $seconds += (int) round(((float) $hours) * 3600);
        }
        if ($minutes !== '') {
            $seconds += (int) round(((float) $minutes) * 60);
        }
        if ($secondsPart !== '') {
            $seconds += (int) $secondsPart;
        }

        return self::guard($seconds, $input);
    }

    public static function format(int $seconds): string
    {
        if ($seconds <= 0) {
            return '0m';
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secondsRemainder = $seconds % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        }
        if ($hours > 0) {
            return "{$hours}h";
        }
        if ($minutes > 0) {
            return "{$minutes}m";
        }

        return "{$secondsRemainder}s";
    }

    public static function formatClock(int $seconds): string
    {
        $seconds = max(0, $seconds);
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secondsRemainder = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secondsRemainder);
    }

    private static function guard(int $seconds, string $input): int
    {
        if ($seconds <= 0) {
            throw ValidationException::withMessages([
                'duration' => __('Duration must be greater than zero.'),
            ]);
        }

        if ($seconds > self::MAX_SECONDS) {
            throw ValidationException::withMessages([
                'duration' => __('Duration cannot exceed 7 days.'),
            ]);
        }

        return $seconds;
    }

    private static function invalid(string $input): ValidationException
    {
        return ValidationException::withMessages([
            'duration' => __('":input" is not a recognised duration. Try formats like "3h 20m", "90m", "1.5h" or "1:30".', ['input' => $input]),
        ]);
    }
}
