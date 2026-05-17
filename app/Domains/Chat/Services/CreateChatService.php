<?php

namespace App\Domains\Chat\Services;

use App\Domains\Chat\Actions\CreateChatAction;
use App\Domains\Chat\Actions\CreateMessageAction;
use App\Domains\Chat\Enums\MessageRole;
use App\Domains\Chat\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class CreateChatService
{
    public function __construct(
        private CreateChatAction $createChat,
        private CreateMessageAction $createMessage,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $attachments
     */
    public function execute(User $user, string $firstMessage, array $attachments = []): Chat
    {
        Validator::make(['message' => $firstMessage], [
            'message' => ['required', 'string', 'max:8000'],
        ])->validate();

        return DB::transaction(function () use ($user, $firstMessage, $attachments) {
            $chat = $this->createChat->execute([
                'user_id' => $user->id,
                'title' => Str::limit(Str::squish($firstMessage), 60, ''),
            ]);

            $this->createMessage->execute([
                'chat_id' => $chat->id,
                'role' => MessageRole::User->value,
                'content' => $firstMessage,
                'attachments' => $attachments !== [] ? $attachments : null,
            ]);

            return $chat;
        });
    }
}
