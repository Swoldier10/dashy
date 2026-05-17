<?php

namespace App\Domains\Chat\Actions;

use App\Domains\Chat\Models\Chat;

class CreateChatAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Chat
    {
        return Chat::create($attributes);
    }
}
