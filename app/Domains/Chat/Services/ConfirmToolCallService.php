<?php

namespace App\Domains\Chat\Services;

use App\Domains\Chat\Actions\UpdateMessageToolCallAction;
use App\Domains\Chat\Ai\Services\AiToolRegistry;
use App\Domains\Chat\Enums\MessageRole;
use App\Domains\Chat\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class ConfirmToolCallService
{
    public function __construct(
        private AiToolRegistry $registry,
        private UpdateMessageToolCallAction $update,
    ) {}

    /**
     * Confirm a pending tool call, optionally applying the user's last-minute
     * edits to the LLM-proposed arguments. The tool's own validate() runs again
     * with the merged arguments — if it rejects them, the call stays pending
     * with validation_errors populated inline; if it accepts them, the
     * normalized result is executed and the message flips to created.
     *
     * @param  array<string, mixed>  $edits  partial overrides for the stored
     *                                       tool_call.arguments (top-level
     *                                       scalar / array keys only)
     * @return array<string, mixed>          the updated tool_call payload
     */
    public function execute(User $actor, int $messageId, array $edits = []): array
    {
        $message = Message::query()
            ->whereHas('chat', fn ($q) => $q->where('user_id', $actor->id))
            ->find($messageId);

        if ($message === null) {
            throw new ModelNotFoundException('Message not found.');
        }

        if ($message->role !== MessageRole::Assistant) {
            throw new AuthorizationException('Only assistant messages can carry tool calls.');
        }

        $toolCall = $message->tool_call;
        if (! is_array($toolCall) || ($toolCall['status'] ?? null) !== 'pending') {
            throw new RuntimeException('Tool call is not pending.');
        }

        $tool = $this->registry->get((string) ($toolCall['name'] ?? ''));
        $arguments = is_array($toolCall['arguments'] ?? null) ? $toolCall['arguments'] : [];
        $merged = array_replace($arguments, $this->normalizeEdits($edits));

        return DB::transaction(function () use ($actor, $message, $toolCall, $tool, $merged): array {
            $result = $tool->validate($actor, $merged, $message->chat);

            if (! $result->valid) {
                $updated = array_merge($toolCall, [
                    'arguments' => $merged,
                    'validation_errors' => $result->errors,
                ]);
                $this->update->execute($message, $updated);

                return $updated;
            }

            $payload = $tool->execute($actor, $result->normalized);

            $updated = array_merge($toolCall, [
                'arguments' => $result->normalized,
                'status' => 'created',
                'result' => $payload,
                'validation_errors' => [],
            ]);
            $this->update->execute($message, $updated);

            return $updated;
        });
    }

    /**
     * Coerce edit values to the shape the tools' validators expect. Without
     * this, HTML form inputs (which arrive as strings) would slip past
     * array_replace and the validator would fall back to its "missing /
     * malformed" branches.
     *
     * @param  array<string, mixed>  $edits
     * @return array<string, mixed>
     */
    private function normalizeEdits(array $edits): array
    {
        $normalized = [];
        foreach ($edits as $key => $value) {
            $normalized[$key] = match ($key) {
                'team_id', 'project_id', 'status_id', 'task_id', 'user_id',
                'target_status_id', 'project_status_id', 'event_id' => $this->toIntOrNull($value),
                'assignee_user_ids', 'task_ids' => $this->toIntList($value),
                'start_date', 'end_date', 'started_at', 'ended_at',
                'start_at', 'end_at', 'recurrence_until' => $this->toDateStringOrNull($value),
                'is_all_day' => $this->toBool($value),
                'name', 'description', 'priority', 'duration', 'notes',
                'category', 'title', 'color', 'location',
                'recurrence_freq' => is_string($value) ? $value : (is_scalar($value) ? (string) $value : null),
                default => $value,
            };
        }

        return $normalized;
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'on', 'yes'], true);
        }
        if (is_int($value)) {
            return $value === 1;
        }

        return false;
    }

    private function toIntOrNull(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        return null;
    }

    /**
     * @return array<int, int>
     */
    private function toIntList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $ints = [];
        foreach ($value as $v) {
            $i = $this->toIntOrNull($v);
            if ($i !== null) {
                $ints[] = $i;
            }
        }

        return array_values(array_unique($ints));
    }

    private function toDateStringOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }
        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
