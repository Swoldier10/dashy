<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Actions\FindUserByEmailAction;
use App\Models\User;

/**
 * Looks up a user by their email address, but only returns them when they
 * share at least one team with the actor. Lets the AI chat ask "who is
 * alice@…?" without exposing a user-existence oracle for arbitrary emails.
 */
final class FindUserByEmailService
{
    public function __construct(
        private FindUserByEmailAction $findByEmail,
        private UserSharesTeamWithService $sharesTeamWith,
    ) {}

    public function execute(User $actor, string $email): ?User
    {
        $email = trim($email);
        if ($email === '') {
            return null;
        }

        $target = $this->findByEmail->execute($email);
        if ($target === null) {
            return null;
        }

        if ($target->is($actor)) {
            return $target;
        }

        return $this->sharesTeamWith->execute($target, $actor) ? $target : null;
    }
}
