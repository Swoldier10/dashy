<?php

namespace App\Domains\Preferences\Services;

use App\Domains\Preferences\Actions\UpsertUserPreferenceAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdateWorkingHoursService
{
    public const PREFERENCE_KEY = 'working_hours';

    /**
     * @var list<string>
     */
    public const DAYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    private const TIME_PATTERN = '/^([01]\d|2[0-3]):[0-5]\d$/';

    public function __construct(
        private UpsertUserPreferenceAction $upsert,
    ) {}

    /**
     * @param  array<string, mixed>  $hours
     * @return array<string, list<array{start: string, end: string}>>
     */
    public function execute(User $user, array $hours): array
    {
        /** @var array<string, array<int, string>> $errors */
        $errors = [];
        /** @var array<string, list<array{start: string, end: string}>> $normalised */
        $normalised = [];

        foreach (array_keys($hours) as $key) {
            if (! in_array($key, self::DAYS, true)) {
                $errors['hours'][] = __('Unknown day in working hours.');
            }
        }

        foreach (self::DAYS as $day) {
            $ranges = $hours[$day] ?? [];

            if (! is_array($ranges)) {
                $errors["hours.$day"][] = __('Working hours for each day must be a list of ranges.');
                $normalised[$day] = [];

                continue;
            }

            $clean = $this->validateAndCleanRanges($day, $ranges, $errors);
            usort($clean, fn ($a, $b) => $a['start'] <=> $b['start']);

            for ($k = 1; $k < count($clean); $k++) {
                if ($clean[$k]['start'] < $clean[$k - 1]['end']) {
                    $errors["hours.$day"][] = __('Time ranges on the same day may not overlap.');
                    break;
                }
            }

            $normalised[$day] = array_values(array_map(
                fn (array $r): array => ['start' => $r['start'], 'end' => $r['end']],
                $clean,
            ));
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return DB::transaction(function () use ($user, $normalised): array {
            $this->upsert->execute($user->id, self::PREFERENCE_KEY, $normalised);

            return $normalised;
        });
    }

    /**
     * @param  array<int|string, mixed>  $ranges
     * @param  array<string, array<int, string>>  $errors
     * @return list<array{start: string, end: string}>
     */
    private function validateAndCleanRanges(string $day, array $ranges, array &$errors): array
    {
        $clean = [];

        foreach ($ranges as $i => $range) {
            if (! is_array($range)) {
                $errors["hours.$day.$i"][] = __('Each range must have a start and end time.');

                continue;
            }

            $start = $range['start'] ?? null;
            $end = $range['end'] ?? null;

            $startOk = is_string($start) && preg_match(self::TIME_PATTERN, $start) === 1;
            $endOk = is_string($end) && preg_match(self::TIME_PATTERN, $end) === 1;

            if (! $startOk) {
                $errors["hours.$day.$i.start"][] = __('Start time must be in HH:MM format.');
            }

            if (! $endOk) {
                $errors["hours.$day.$i.end"][] = __('End time must be in HH:MM format.');
            }

            if (! $startOk || ! $endOk) {
                continue;
            }

            if ($end <= $start) {
                $errors["hours.$day.$i.end"][] = __('End time must be after the start time.');

                continue;
            }

            $clean[] = ['start' => $start, 'end' => $end];
        }

        return $clean;
    }
}
