<?php

namespace App\Domains\Auth\Actions;

use App\Models\User;

class FindUserByGoogleIdAction
{
    public function execute(string $googleId): ?User
    {
        return User::where('google_id', $googleId)->first();
    }
}
