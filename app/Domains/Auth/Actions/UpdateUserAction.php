<?php

namespace App\Domains\Auth\Actions;

use App\Models\User;

class UpdateUserAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(User $user, array $attributes): User
    {
        $user->forceFill($attributes)->save();

        return $user->refresh();
    }
}
