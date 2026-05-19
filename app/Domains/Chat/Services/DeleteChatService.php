<?php

namespace App\Domains\Chat\Services;

use App\Domains\Chat\Actions\DeleteChatAction;
use App\Domains\Chat\Actions\FindChatForUserAction;
use App\Domains\Chat\Actions\ListChatAttachmentPathsAction;
use App\Domains\Chat\Models\Chat;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class DeleteChatService
{
    public function __construct(
        private FindChatForUserAction $findAction,
        private DeleteChatAction $deleteAction,
        private ListChatAttachmentPathsAction $listAttachmentPaths,
    ) {}

    public function execute(User $user, int $chatId): void
    {
        $chat = $this->findAction->execute($user, $chatId);
        if ($chat === null) {
            throw new ModelNotFoundException;
        }

        $this->delete($chat);
    }

    public function delete(Chat $chat): void
    {
        DB::transaction(function () use ($chat) {
            $this->cleanUpAttachmentFiles($chat);
            $this->deleteAction->execute($chat);
        });
    }

    private function cleanUpAttachmentFiles(Chat $chat): void
    {
        $paths = $this->listAttachmentPaths->execute($chat);

        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }
    }
}
