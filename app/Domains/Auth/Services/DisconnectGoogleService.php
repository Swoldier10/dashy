<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Actions\UpdateUserAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DisconnectGoogleService
{
    public function __construct(
        private UpdateUserAction $updateUser,
    ) {}

    public function execute(User $user): User
    {
        if ($user->password === null) {
            throw ValidationException::withMessages([
                'google' => __('Set a password before disconnecting Google so you can still sign in.'),
            ]);
        }

        return DB::transaction(fn () => $this->updateUser->execute($user, [
            'google_id' => null,
        ]));
    }
}
