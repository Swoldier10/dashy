<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Actions\FindUserByIdAction;
use App\Models\User;

/**
 * Returns a user by ID without any authorization scope — only safe for
 * internal callers (background jobs, console commands) that have already
 * established why the lookup is allowed (e.g. a queue payload referencing
 * the connection's user_id).
 */
final class FindUserByIdService
{
    public function __construct(
        private FindUserByIdAction $find,
    ) {}

    public function execute(int $id): ?User
    {
        return $this->find->execute($id);
    }
}
