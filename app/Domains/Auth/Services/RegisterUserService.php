<?php

namespace App\Domains\Auth\Services;

use App\Concerns\PasswordValidationRules;
use App\Domains\Auth\Actions\CreateUserAction;
use App\Domains\Auth\Enums\Salutation;
use App\Domains\Teams\Services\EnsurePersonalTeamService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

final class RegisterUserService implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function __construct(
        private CreateUserAction $createUser,
        private EnsurePersonalTeamService $ensurePersonalTeam,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        $validated = Validator::make($input, [
            'salutation' => ['nullable', Rule::enum(Salutation::class)],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password' => $this->passwordRules(),
            'terms' => ['required', 'accepted'],
        ], [
            'salutation.enum' => __('Please choose a valid salutation.'),
            'terms.required' => __('You must accept the terms to create an account.'),
            'terms.accepted' => __('You must accept the terms to create an account.'),
            'first_name.max' => __('First name must be 80 characters or fewer.'),
            'last_name.max' => __('Last name must be 80 characters or fewer.'),
            'email.unique' => __('An account with this email already exists.'),
        ])->validate();

        $salutation = $validated['salutation'] ?? null;
        $salutationLabel = $salutation !== null ? Salutation::from($salutation)->label() : null;

        $attributes = [
            'salutation' => $salutation,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => $this->composeLegacyName(
                $salutationLabel,
                $validated['first_name'],
                $validated['last_name'],
                $validated['email'],
            ),
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];

        return DB::transaction(function () use ($attributes) {
            $user = $this->createUser->execute($attributes);
            $this->ensurePersonalTeam->execute($user);

            return $user;
        });
    }

    private function composeLegacyName(
        ?string $salutationLabel,
        string $firstName,
        string $lastName,
        string $emailFallback,
    ): string {
        $name = trim(implode(' ', array_filter([$salutationLabel, $firstName, $lastName])));

        return $name !== '' ? $name : $emailFallback;
    }
}
