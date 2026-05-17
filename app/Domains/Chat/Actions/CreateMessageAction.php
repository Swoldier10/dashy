<?php

namespace App\Domains\Chat\Actions;

use App\Domains\Chat\Models\Message;

class CreateMessageAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Message
    {
        return Message::create($attributes);
    }
}
