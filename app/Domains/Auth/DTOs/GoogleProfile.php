<?php

namespace App\Domains\Auth\DTOs;

use Laravel\Socialite\Two\User as SocialiteUser;

final readonly class GoogleProfile
{
    public function __construct(
        public string $id,
        public string $email,
        public bool $emailVerified,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $avatar,
    ) {}

    public static function fromSocialite(SocialiteUser $socialiteUser): self
    {
        $raw = is_array($socialiteUser->user) ? $socialiteUser->user : [];
        [$first, $last] = self::splitName($socialiteUser->getName());

        return new self(
            id: (string) $socialiteUser->getId(),
            email: (string) $socialiteUser->getEmail(),
            emailVerified: (bool) ($raw['email_verified'] ?? false),
            firstName: $raw['given_name'] ?? $first,
            lastName: $raw['family_name'] ?? $last,
            avatar: $socialiteUser->getAvatar(),
        );
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private static function splitName(?string $name): array
    {
        if (! $name) {
            return [null, null];
        }

        $parts = preg_split('/\s+/', trim($name), 2) ?: [];

        return [$parts[0] ?? null, $parts[1] ?? null];
    }
}
