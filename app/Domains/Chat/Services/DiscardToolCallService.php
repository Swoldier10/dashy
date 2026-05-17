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

final class DiscardToolCallService
{
    public function __construct(
        private UpdateMessageToolCallAction $update,
    ) {}

    /**
     * @return array<string, mixed>  the updated tool_call payload
     */
    public function execute(User $actor, int $messageId): array
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

        return DB::transaction(function () use ($message, $toolCall): array {
            $updated = array_merge($toolCall, ['status' => 'discarded']);
            $this->update->execute($message, $updated);

            return $updated;
        });
    }
}
