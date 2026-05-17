<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Calendar\Enums\EventColor;
use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Services\FindEventService;
use App\Domains\Calendar\Services\UpdateEventService;
use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Models\User;
use Carbon\CarbonImmutable;
use Throwable;

/**
 * CONFIRM-WRITE. Updates one or more fields on an existing calendar event.
 * Affects the whole recurring series, not a single occurrence — the system
 * prompt warns the LLM (and through it the user) about this.
 */
final class UpdateEventTool implements AiTool
{
    private const UPDATABLE_FIELDS = [
        'title', 'description', 'start_at', 'end_at',
        'is_all_day', 'color', 'location',
        'recurrence_freq', 'recurrence_until',
    ];

    public function __construct(
        private FindEventService $findEvent,
        private UpdateEventService $updateEvent,
    ) {}

    public function name(): string
    {
        return 'update_event';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Update one or more fields of an existing calendar event. Pass event_id plus any subset of '
            .'fields to change. Use list_events first to find the event id — never guess. Updates affect '
            .'the entire recurring series. title and description MUST be German.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'event_id' => ['type' => 'integer'],
                'title' => ['type' => 'string', 'maxLength' => 200],
                'description' => ['type' => 'string', 'maxLength' => 5000],
                'start_at' => ['type' => 'string', 'description' => 'ISO 8601 YYYY-MM-DDTHH:mm'],
                'end_at' => ['type' => 'string', 'description' => 'ISO 8601 YYYY-MM-DDTHH:mm'],
                'is_all_day' => ['type' => 'boolean'],
                'color' => [
                    'type' => 'string',
                    'enum' => array_map(fn (EventColor $c) => $c->value, EventColor::cases()),
                ],
                'location' => ['type' => 'string', 'maxLength' => 200],
                'recurrence_freq' => [
                    'type' => 'string',
                    'enum' => array_map(fn (RecurrenceFreq $r) => $r->value, RecurrenceFreq::cases()),
                ],
                'recurrence_until' => ['type' => 'string', 'format' => 'date'],
            ],
            'required' => ['event_id'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $eventId = $arguments['event_id'] ?? null;
        if (! is_int($eventId) && ! (is_string($eventId) && ctype_digit($eventId))) {
            return AiToolValidationResult::fail(['event_id is required and must be an integer.']);
        }
        $eventId = (int) $eventId;

        try {
            $event = $this->findEvent->execute($user, $eventId);
        } catch (Throwable) {
            return AiToolValidationResult::fail(['Event not found or not accessible.']);
        }

        $normalized = ['event_id' => $eventId];

        if (array_key_exists('title', $arguments)) {
            $title = $arguments['title'];
            if (! is_string($title) || trim($title) === '') {
                return AiToolValidationResult::fail(['title cannot be empty.']);
            }
            $normalized['title'] = trim($title);
        }

        if (array_key_exists('description', $arguments)) {
            $description = $arguments['description'];
            $normalized['description'] = is_string($description) && trim($description) !== ''
                ? trim($description)
                : null;
        }

        $startAt = null;
        if (array_key_exists('start_at', $arguments)) {
            $startAt = $this->parseDateTime($arguments['start_at']);
            if ($startAt === null) {
                return AiToolValidationResult::fail(['start_at must be a valid ISO 8601 date-time.']);
            }
            $normalized['start_at'] = $startAt->format('Y-m-d\TH:i');
        }

        $endAt = null;
        if (array_key_exists('end_at', $arguments)) {
            $endAt = $this->parseDateTime($arguments['end_at']);
            if ($endAt === null) {
                return AiToolValidationResult::fail(['end_at must be a valid ISO 8601 date-time.']);
            }
            $normalized['end_at'] = $endAt->format('Y-m-d\TH:i');
        }

        // Cross-check start/end against each other (or against the persisted value
        // when only one is provided) so we surface ordering errors on the card
        // instead of letting the service throw mid-confirm.
        $effectiveStart = $startAt ?? CarbonImmutable::instance($event->start_at);
        $effectiveEnd = $endAt ?? CarbonImmutable::instance($event->end_at);
        if ($effectiveEnd->lt($effectiveStart)) {
            return AiToolValidationResult::fail(['end_at must be on or after start_at.']);
        }

        if (array_key_exists('is_all_day', $arguments)) {
            $normalized['is_all_day'] = filter_var($arguments['is_all_day'], FILTER_VALIDATE_BOOLEAN);
        }

        if (array_key_exists('color', $arguments)) {
            $color = $arguments['color'];
            if (! is_string($color) || EventColor::tryFrom($color) === null) {
                return AiToolValidationResult::fail(['color is not a valid event color.']);
            }
            $normalized['color'] = $color;
        }

        if (array_key_exists('location', $arguments)) {
            $location = $arguments['location'];
            $normalized['location'] = is_string($location) && trim($location) !== ''
                ? trim($location)
                : null;
        }

        if (array_key_exists('recurrence_freq', $arguments)) {
            $freq = $arguments['recurrence_freq'];
            if (! is_string($freq) || RecurrenceFreq::tryFrom($freq) === null) {
                return AiToolValidationResult::fail(['recurrence_freq is not a valid frequency.']);
            }
            $normalized['recurrence_freq'] = $freq;
        }

        if (array_key_exists('recurrence_until', $arguments)) {
            $until = $this->parseDate($arguments['recurrence_until']);
            $effectiveFreq = $normalized['recurrence_freq']
                ?? $event->recurrence_freq?->value
                ?? RecurrenceFreq::None->value;

            if ($effectiveFreq === RecurrenceFreq::None->value) {
                // Drop irrelevant recurrence_until to avoid a service-side validation error.
                $normalized['recurrence_until'] = null;
            } else {
                $normalized['recurrence_until'] = $until?->toDateString();
            }
        }

        $changedKeys = array_intersect(self::UPDATABLE_FIELDS, array_keys($normalized));
        if ($changedKeys === []) {
            return AiToolValidationResult::fail(['Nothing to update — supply at least one field besides event_id.']);
        }

        return AiToolValidationResult::ok($normalized);
    }

    public function execute(User $user, array $arguments): array
    {
        $eventId = (int) $arguments['event_id'];

        $attrs = array_intersect_key($arguments, array_flip(self::UPDATABLE_FIELDS));

        $this->updateEvent->execute($user, $eventId, $attrs);

        return [
            'event_id' => $eventId,
            'changes' => array_keys($attrs),
        ];
    }

    private function parseDateTime(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            $parsed = CarbonImmutable::parse($value);
        } catch (Throwable) {
            return null;
        }

        $today = CarbonImmutable::today();
        if ($parsed->lt($today->subYears(5)) || $parsed->gt($today->addYears(5))) {
            return null;
        }

        return $parsed;
    }

    private function parseDate(mixed $value): ?CarbonImmutable
    {
        return $this->parseDateTime($value)?->startOfDay();
    }
}
