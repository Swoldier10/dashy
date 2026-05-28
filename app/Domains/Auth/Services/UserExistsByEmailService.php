<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Actions\FindUserByEmailAction;

/**
 * Boolean existence check for an email. No privacy filter — used by flows
 * where the recipient of an invitation has already authorized us (via the
 * tokenized link) to disclose whether their email maps to a Dashy account.
 */
final class UserExistsByEmailService
{
    public function __construct(
        private FindUserByEmailAction $findByEmail,
    ) {}

    public function execute(string $email): bool
    {
        $email = trim($email);
        if ($email === '') {
            return false;
        }

        return $this->findByEmail->execute($email) !== null;
    }
}
