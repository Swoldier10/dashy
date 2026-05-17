<?php

namespace App\Domains\Chat\Services;

use App\Domains\Chat\Actions\DeleteChatAction;
use App\Domains\Chat\Actions\FindChatForUserAction;
use App\Domains\Chat\Models\Chat;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class DeleteChatService
{
    public function __construct(
        private FindChatForUserAction $find,
        private DeleteChatAction $delete,
    ) {}

    public function execute(User $user, int $chatId): void
    {
        $chat = $this->find->execute($user, $chatId);
        if ($chat === null) {
            throw new ModelNotFoundException;
        }

        DB::transaction(function () use ($chat) {
            $this->cleanUpAttachmentFiles($chat);
            $this->delete->execute($chat);
        });
    }

    private function cleanUpAttachmentFiles(Chat $chat): void
    {
        $paths = $chat->messages()
            ->whereNotNull('attachments')
            ->pluck('attachments')
            ->flatten(1)
            ->pluck('path')
            ->filter()
            ->values()
            ->all();

        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }
    }
}
