<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Calendar\Enums\EventColor;
use App\Domains\Calendar\Enums\RecurrenceFreq;
use App\Domains\Calendar\Services\CreateEventService;
use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\Contracts\PresentsToolCard;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Models\User;
use Carbon\CarbonImmutable;
use Throwable;

/**
 * CONFIRM-WRITE. Creates a calendar event in the user's personal calendar.
 * Distinct from create_task: events are time-bound (start + end time of day),
 * tasks are deadline-bound. See system prompt for the discriminator.
 */
final class CreateEventTool implements AiTool, PresentsToolCard
{
    public function __construct(
        private CreateEventService $createEvent,
    ) {}

    public function name(): string
    {
        return 'create_event';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Create a calendar event for the current user. Use when the user wants to schedule '
            .'something at a specific time of day (meetings, appointments, time-blocks). For deadline-bound '
            .'todos without a clock time, use create_task instead. `title` and `description` MUST be '
            .'written in German. start_at / end_at use ISO 8601 24h format YYYY-MM-DDTHH:mm.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string', 'maxLength' => 200],
                'description' => ['type' => 'string', 'maxLength' => 5000],
                'start_at' => ['type' => 'string', 'description' => 'ISO 8601 YYYY-MM-DDTHH:mm, 24h'],
                'end_at' => ['type' => 'string', 'description' => 'ISO 8601 YYYY-MM-DDTHH:mm; defaults to start_at + 1 hour'],
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
            'required' => ['title', 'start_at'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $errors = [];

        $title = $arguments['title'] ?? null;
        if (! is_string($title) || trim($title) === '') {
            $errors[] = 'title is required.';
        }

        $start = $this->parseDateTime($arguments['start_at'] ?? null);
        if ($start === null) {
            $errors[] = 'start_at is required and must be a valid ISO 8601 date-time.';
        }

        if ($errors !== []) {
            return AiToolValidationResult::fail($errors);
        }

        // end_at: default to start + 1h when missing, unparseable, or before start.
        $end = $this->parseDateTime($arguments['end_at'] ?? null);
        if ($end === null || $end->lt($start)) {
            $end = $start->addHour();
        }

        $color = $arguments['color'] ?? null;
        if (! is_string($color) || EventColor::tryFrom($color) === null) {
            $color = EventColor::Danube->value;
        }

        $recurrenceFreq = $arguments['recurrence_freq'] ?? null;
        if (! is_string($recurrenceFreq) || RecurrenceFreq::tryFrom($recurrenceFreq) === null) {
            $recurrenceFreq = RecurrenceFreq::None->value;
        }

        $recurrenceUntil = null;
        if ($recurrenceFreq !== RecurrenceFreq::None->value) {
            $parsedUntil = $this->parseDate($arguments['recurrence_until'] ?? null);
            if ($parsedUntil !== null && $parsedUntil->gte($start->startOfDay())) {
                $recurrenceUntil = $parsedUntil->toDateString();
            }
        }

        $description = $arguments['description'] ?? null;
        if (! is_string($description) || trim($description) === '') {
            $description = null;
        } else {
            $description = trim($description);
        }

        $location = $arguments['location'] ?? null;
        if (! is_string($location) || trim($location) === '') {
            $location = null;
        } else {
            $location = trim($location);
        }

        $isAllDay = $arguments['is_all_day'] ?? false;
        $isAllDay = filter_var($isAllDay, FILTER_VALIDATE_BOOLEAN);

        return AiToolValidationResult::ok([
            'title' => trim((string) $title),
            'description' => $description,
            'start_at' => $start->format('Y-m-d\TH:i'),
            'end_at' => $end->format('Y-m-d\TH:i'),
            'is_all_day' => $isAllDay,
            'color' => $color,
            'location' => $location,
            'recurrence_freq' => $recurrenceFreq,
            'recurrence_until' => $recurrenceUntil,
        ]);
    }

    public function execute(User $user, array $arguments): array
    {
        $event = $this->createEvent->execute($user, [
            'title' => $arguments['title'],
            'description' => $arguments['description'] ?? null,
            'start_at' => $arguments['start_at'],
            'end_at' => $arguments['end_at'],
            'is_all_day' => (bool) ($arguments['is_all_day'] ?? false),
            'color' => $arguments['color'] ?? EventColor::Danube->value,
            'location' => $arguments['location'] ?? null,
            'recurrence_freq' => $arguments['recurrence_freq'] ?? RecurrenceFreq::None->value,
            'recurrence_until' => $arguments['recurrence_until'] ?? null,
        ]);

        return ['event_id' => $event->id];
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

    public function presentCard(array $toolCall, User $user): array
    {
        $args = is_array($toolCall['arguments'] ?? null) ? $toolCall['arguments'] : [];

        return [
            'name' => 'create_event',
            'status' => (string) ($toolCall['status'] ?? 'pending'),
            'arguments' => $args,
            'available_colors' => array_map(fn (EventColor $c) => [
                'value' => $c->value,
                'label' => $c->label(),
                'var' => $c->colorVar(),
            ], EventColor::cases()),
            'available_recurrence_freqs' => array_map(fn (RecurrenceFreq $r) => [
                'value' => $r->value,
                'label' => $r->label(),
            ], RecurrenceFreq::cases()),
            'validation_errors' => (array) ($toolCall['validation_errors'] ?? []),
            'result' => is_array($toolCall['result'] ?? null) ? $toolCall['result'] : [],
        ];
    }
}
