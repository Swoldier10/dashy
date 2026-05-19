<?php

namespace App\Domains\Chat\Actions;

use App\Domains\Chat\Models\Chat;
use Carbon\CarbonInterface;

class SetChatStopRequestedAtAction
{
    public function execute(Chat $chat, ?CarbonInterface $timestamp): void
    {
        $chat->forceFill(['stop_requested_at' => $timestamp])->save();
    }
}
