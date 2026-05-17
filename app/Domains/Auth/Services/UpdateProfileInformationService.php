<?php

namespace App\Domains\Auth\Services;

use App\Concerns\ProfileValidationRules;
use App\Domains\Auth\Actions\UpdateUserAction;
use App\Domains\Auth\Enums\Salutation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class UpdateProfileInformationService
{
    use ProfileValidationRules;

    public function __construct(
        private UpdateUserAction $updateUser,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public function execute(User $user, array $input): User
    {
        $validated = Validator::make($input, [
            'salutation' => $this->salutationRules(),
            'first_name' => $this->firstNameRules(),
            'last_name' => $this->lastNameRules(),
            'email' => $this->emailRules($user->id),
        ], [
            'salutation.enum' => __('Please choose a valid salutation.'),
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
        ];

        if ($user->email !== $validated['email']) {
            $attributes['email_verified_at'] = null;
        }

        return DB::transaction(fn () => $this->updateUser->execute($user, $attributes));
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
