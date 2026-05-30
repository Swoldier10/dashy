<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Actions\FindUserByEmailAction;
use App\Models\User;

/**
 * Raw email-to-user lookup with no actor scoping. Use this when the caller
 * has its own authorization for the lookup (e.g. a team owner inviting any
 * user by email). For chat-style lookups that must not expose user existence
 * to strangers, use {@see FindUserByEmailService}, which gates by
 * shared-team membership.
 */
final class LookupUserByEmailService
{
    public function __construct(
        private FindUserByEmailAction $find,
    ) {}

    public function execute(string $email): ?User
    {
        return $this->find->execute($email);
    }
}
