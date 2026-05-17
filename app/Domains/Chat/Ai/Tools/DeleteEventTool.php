<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Calendar\Services\DeleteEventService;
use App\Domains\Calendar\Services\FindEventService;
use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Models\User;
use Throwable;

/**
 * CONFIRM-WRITE (destructive). Permanently deletes a calendar event. If the
 * event is recurring, the entire series is removed — surface this in the
 * destructive card summary so the user reviews exactly what disappears.
 */
final class DeleteEventTool implements AiTool
{
    public function __construct(
        private FindEventService $findEvent,
        private DeleteEventService $deleteEvent,
    ) {}

    public function name(): string
    {
        return 'delete_event';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::ConfirmWrite;
    }

    public function description(): string
    {
        return 'Permanently delete a calendar event. Destructive — if the event recurs, the whole series '
            .'is removed. Use list_events first to identify the event id; never guess.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'event_id' => ['type' => 'integer'],
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
            $this->findEvent->execute($user, $eventId);
        } catch (Throwable) {
            return AiToolValidationResult::fail(['Event not found or not accessible.']);
        }

        return AiToolValidationResult::ok(['event_id' => $eventId]);
    }

    public function execute(User $user, array $arguments): array
    {
        $eventId = (int) $arguments['event_id'];
        $this->deleteEvent->execute($user, $eventId);

        return ['event_id' => $eventId, 'deleted' => true];
    }
}
