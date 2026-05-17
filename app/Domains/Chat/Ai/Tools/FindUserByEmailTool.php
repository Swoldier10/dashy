<?php

namespace App\Domains\Chat\Ai\Tools;

use App\Domains\Auth\Services\FindUserByEmailService;
use App\Domains\Chat\Ai\Contracts\AiTool;
use App\Domains\Chat\Ai\DTOs\AiToolValidationResult;
use App\Domains\Chat\Ai\Enums\AiToolExecutionMode;
use App\Domains\Chat\Models\Chat;
use App\Models\User;

/**
 * AUTO-READ. Resolves an email address to a user, but only when the target
 * user shares at least one team with the actor. Returns null when the email
 * is unknown or out-of-scope — never exposes existence of arbitrary accounts.
 */
final class FindUserByEmailTool implements AiTool
{
    public function __construct(
        private FindUserByEmailService $findByEmail,
    ) {}

    public function name(): string
    {
        return 'find_user_by_email';
    }

    public function executionMode(): AiToolExecutionMode
    {
        return AiToolExecutionMode::AutoRead;
    }

    public function description(): string
    {
        return 'Resolve an email address to a user the current user can act on. Scoped to people that '
            .'share a team with the user. Returns null if there is no match.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'email' => ['type' => 'string', 'format' => 'email'],
            ],
            'required' => ['email'],
            'additionalProperties' => false,
        ];
    }

    public function validate(User $user, array $arguments, ?Chat $chat = null): AiToolValidationResult
    {
        $email = $arguments['email'] ?? null;
        if (! is_string($email) || trim($email) === '') {
            return AiToolValidationResult::fail(['email is required and must be a non-empty string.']);
        }

        return AiToolValidationResult::ok(['email' => trim($email)]);
    }

    public function execute(User $user, array $arguments): array
    {
        $target = $this->findByEmail->execute($user, (string) $arguments['email']);

        if ($target === null) {
            return ['found' => false];
        }

        return [
            'found' => true,
            'user' => [
                'id' => $target->id,
                'name' => (string) $target->name,
                'email' => (string) $target->email,
            ],
        ];
    }
}
