<?php

namespace App\Domains\Auth\Services;

use App\Concerns\PasswordValidationRules;
use App\Domains\Auth\Actions\UpdateUserAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class UpdatePasswordService
{
    use PasswordValidationRules;

    public function __construct(
        private UpdateUserAction $updateUser,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public function execute(User $user, array $input): User
    {
        $rules = $user->password === null
            ? ['password' => $this->passwordRules()]
            : [
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ];

        $validated = Validator::make($input, $rules)->validate();

        return DB::transaction(fn () => $this->updateUser->execute($user, [
            'password' => $validated['password'],
        ]));
    }
}
