<?php

namespace App\Domains\Chat\Services;

use App\Domains\Chat\Actions\FindLatestUserMessageIdAction;
use App\Domains\Chat\Models\Chat;

final class FindLatestUserMessageIdService
{
    public function __construct(
        private FindLatestUserMessageIdAction $find,
    ) {}

    public function execute(Chat $chat): ?int
    {
        return $this->find->execute($chat);
    }
}
