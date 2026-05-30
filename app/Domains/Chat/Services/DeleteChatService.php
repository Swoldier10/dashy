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
        // Resolve the attachment paths while the rows still exist, delete the
        // chat inside the transaction, then remove the files only AFTER the
        // delete has durably committed. Storage::delete is irreversible, so it
        // must never run inside a transaction that could roll back and leave
        // surviving rows pointing at already-deleted files.
        $paths = $this->listAttachmentPaths->execute($chat);

        DB::transaction(fn () => $this->deleteAction->execute($chat));

        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }
    }
}
