<?php

namespace App\Domains\Chat\Actions;

use App\Domains\Chat\Models\Message;

class UpdateMessageToolCallAction
{
    /**
     * @param  array<string, mixed>  $toolCall
     */
    public function execute(Message $message, array $toolCall): void
    {
        $message->forceFill(['tool_call' => $toolCall])->save();
    }
}
