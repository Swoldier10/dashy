<?php

namespace App\Domains\Auth\Actions;

use App\Models\User;

class FindUserByEmailAction
{
    public function execute(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}
