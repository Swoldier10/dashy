<?php

namespace App\Domains\Chat\Services;

use App\Domains\Chat\Actions\FindChatForUserAction;
use App\Domains\Chat\Actions\SetChatStopRequestedAtAction;
use App\Domains\Chat\Models\Chat;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final class UpdateChatStopStateService
{
    public function __construct(
        private FindChatForUserAction $findAction,
        private SetChatStopRequestedAtAction $setStopAction,
    ) {}

    public function requestStop(User $actor, Chat $chat): void
    {
        $this->authorize($actor, $chat);

        DB::transaction(fn () => $this->setStopAction->execute($chat, now()));
    }

    public function clearStop(User $actor, Chat $chat): void
    {
        $this->authorize($actor, $chat);

        DB::transaction(fn () => $this->setStopAction->execute($chat, null));
    }

    private function authorize(User $actor, Chat $chat): void
    {
        if ($this->findAction->execute($actor, $chat->id) === null) {
            throw new ModelNotFoundException;
        }
    }
}
