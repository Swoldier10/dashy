<?php

namespace App\Domains\Auth\DTOs;

use App\Models\User;

final readonly class SocialAuthResult
{
    public function __construct(
        public User $user,
        public bool $isNewUser,
    ) {}
}
