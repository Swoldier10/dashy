<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Calendar\DTOs\EventOccurrence;
use App\Domains\Calendar\Services\ListEventsForUserInRangeService;
use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Models\User;
use Carbon\CarbonImmutable;
use Throwable;

/**
 * AUTO-READ. Lists calendar event occurrences in a date range. Use before
 * suggesting a new event to detect conflicts, or to answer "what's on my
 * calendar?" type questions. The range is clamped to at most 90 days so the
 * LLM context never blows up on long recurring series.
 */
final class ListEventsTool implements AiTool
{
    private const MAX_RANGE_DAYS = 90;

    public function __construct(
        private ListEventsForUserInRangeService $listEvents,
    ) {}

    public function name(): string
    {
        return 'list_events';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::AutoRead;
    }

    public function description(): string
    {
        return 'List the user\'s calendar event occurrences in a date range. Required: from. Optional: to '
            .'(defaults to from + 7 days). Range is clamped to 90 days. Use to check for conflicts before '
            .'create_event, or to answer "what\'s on my calendar?".';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'from' => ['type' => 'string', 'format' => 'date', 'description' => 'ISO YYYY-MM-DD'],
                'to' => ['type' => 'string', 'format' => 'date', 'description' => 'ISO YYYY-MM-DD; defaults to from + 7 days'],
            ],
            'required' => ['from'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $from = $this->parseDate($arguments['from'] ?? null);
        if ($from === null) {
            return AiToolValidationResult::fail(['from is required and must be a valid YYYY-MM-DD date.']);
        }

        $to = $this->parseDate($arguments['to'] ?? null);
        if ($to === null) {
            $to = $from->addDays(7);
        }

        if ($to->lt($from)) {
            return AiToolValidationResult::fail(['to must be on or after from.']);
        }

        // Clamp the range to MAX_RANGE_DAYS to keep response size sane.
        $maxTo = $from->addDays(self::MAX_RANGE_DAYS);
        if ($to->gt($maxTo)) {
            $to = $maxTo;
        }

        return AiToolValidationResult::ok([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }

    public function execute(User $user, array $arguments): array
    {
        $from = CarbonImmutable::parse($arguments['from'])->startOfDay();
        $to = CarbonImmutable::parse($arguments['to'])->endOfDay();

        $occurrences = $this->listEvents->execute($user, $from, $to);

        $events = array_map(fn (EventOccurrence $occ) => [
            'id' => $occ->event->id,
            'title' => (string) $occ->event->title,
            'start_at' => $occ->startAt->format('Y-m-d\TH:i'),
            'end_at' => $occ->endAt->format('Y-m-d\TH:i'),
            'is_all_day' => (bool) $occ->event->is_all_day,
            'color' => $occ->event->color?->value,
            'location' => $occ->event->location,
            'recurrence_freq' => $occ->event->recurrence_freq?->value,
        ], $occurrences);

        return [
            'count' => count($events),
            'events' => $events,
        ];
    }

    private function parseDate(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value)->startOfDay();
        } catch (Throwable) {
            return null;
        }
    }
}
