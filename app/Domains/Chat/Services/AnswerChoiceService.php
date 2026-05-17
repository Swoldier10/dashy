<?php

namespace App\Domains\Chat\Services;

use App\Domains\Chat\Actions\UpdateMessageToolCallAction;
use App\Domains\Chat\Enums\MessageRole;
use App\Domains\Chat\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class AnswerChoiceService
{
    public function __construct(
        private UpdateMessageToolCallAction $update,
    ) {}

    /**
     * Record the user's selection on a pending ask_user_choice tool call and
     * return the chosen option label so the caller can post it as the user's
     * next message.
     */
    public function execute(User $actor, int $messageId, int $optionIndex): string
    {
        return DB::transaction(function () use ($actor, $messageId, $optionIndex): string {
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
            if (! is_array($toolCall)
                || ($toolCall['name'] ?? null) !== 'ask_user_choice'
                || ($toolCall['status'] ?? null) !== 'pending') {
                throw new RuntimeException('No pending ask_user_choice on this message.');
            }

            $options = $toolCall['arguments']['options'] ?? null;
            if (! is_array($options) || ! array_key_exists($optionIndex, $options) || ! is_string($options[$optionIndex])) {
                throw new RuntimeException('Invalid option index.');
            }

            $label = (string) $options[$optionIndex];

            $updated = array_merge($toolCall, [
                'status' => 'answered',
                'result' => [
                    'choice_index' => $optionIndex,
                    'choice_label' => $label,
                ],
            ]);

            $this->update->execute($message, $updated);

            return $label;
        });
    }
}
