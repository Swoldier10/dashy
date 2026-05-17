<?php

namespace App\Domains\Auth\Actions;

use App\Models\User;

class DeleteUserAction
{
    public function execute(User $user): void
    {
        $user->delete();
    }
}
