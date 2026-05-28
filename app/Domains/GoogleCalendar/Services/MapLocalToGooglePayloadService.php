<?php

namespace App\Domains\GoogleCalendar\Services;

use App\Domains\Calendar\Enums\EventColor;
use App\Domains\Calendar\Models\Event;
use App\Domains\Tasks\Models\Task;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Translates a local Event or Task into a Google Calendar event payload.
 * Carries `extendedProperties.private.dashyType` and `dashyLocalId` so the
 * pull side can identify Dashy-origin events on the round-trip.
 */
final class MapLocalToGooglePayloadService
{
    /**
     * Maps EventColor enum cases to Google Calendar event colorId values
     * (1–11). Approximations; Google's palette doesn't match ours exactly.
     */
    private const COLOR_MAP = [
        'danube' => '7',    // Peacock blue
        'torea' => '9',     // Blueberry
        'shilo' => '4',     // Flamingo (pink-red)
        'success' => '10',  // Basil (green)
        'warning' => '5',   // Banana (yellow)
        'error' => '11',    // Tomato (red)
    ];

    /**
     * @return array<string, mixed>
     */
    public function execute(Model $syncable): array
    {
        if ($syncable instanceof Event) {
            return $this->fromEvent($syncable);
        }

        if ($syncable instanceof Task) {
            return $this->fromTask($syncable);
        }

        throw new \InvalidArgumentException('Unsupported syncable type: '.$syncable::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function fromEvent(Event $event): array
    {
        $payload = [
            'summary' => $event->title,
            'description' => $event->description ?? '',
            'location' => $event->location ?? '',
            'start' => $this->boundaryPayload($event->start_at, $event->is_all_day, isEnd: false),
            'end' => $this->boundaryPayload($event->end_at, $event->is_all_day, isEnd: true),
            'colorId' => $this->mapColor($event->color),
            'extendedProperties' => [
                'private' => [
                    'dashyType' => 'event',
                    'dashyLocalId' => (string) $event->id,
                ],
            ],
        ];

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function fromTask(Task $task): array
    {
        $start = $task->start_date;
        $end = $task->end_date ?? $task->start_date?->copy()->addHour();

        return [
            'summary' => $task->name,
            'description' => $task->description ?? '',
            'start' => $this->boundaryPayload($start, isAllDay: false, isEnd: false),
            'end' => $this->boundaryPayload($end, isAllDay: false, isEnd: true),
            'colorId' => self::COLOR_MAP['danube'],
            'extendedProperties' => [
                'private' => [
                    'dashyType' => 'task',
                    'dashyLocalId' => (string) $task->id,
                ],
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function boundaryPayload(?CarbonInterface $value, bool $isAllDay, bool $isEnd): array
    {
        if ($value === null) {
            return [];
        }

        if ($isAllDay) {
            // Google treats end.date as exclusive — bump by one day on the end side.
            $date = $isEnd ? $value->copy()->addDay() : $value;

            return ['date' => $date->format('Y-m-d')];
        }

        return [
            'dateTime' => $value->copy()->toRfc3339String(),
            'timeZone' => (string) config('app.timezone'),
        ];
    }

    private function mapColor(?EventColor $color): string
    {
        return self::COLOR_MAP[$color?->value] ?? self::COLOR_MAP['danube'];
    }
}
