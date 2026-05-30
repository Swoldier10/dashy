<?php

namespace App\Domains\Auth\Actions;

use App\Models\User;

class FindUserByIdAction
{
    public function execute(int $id): ?User
    {
        return User::query()->find($id);
    }
}
