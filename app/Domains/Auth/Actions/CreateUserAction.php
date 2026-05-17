<?php

namespace App\Domains\Auth\Actions;

use App\Models\User;

class CreateUserAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): User
    {
        $user = new User;
        $user->forceFill($attributes);
        $user->save();

        return $user->refresh();
    }
}
