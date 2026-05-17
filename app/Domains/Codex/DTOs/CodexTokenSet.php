<?php

namespace App\Domains\Codex\DTOs;

use Carbon\CarbonImmutable;

final readonly class CodexTokenSet
{
    public function __construct(
        public string $accessToken,
        public ?string $refreshToken,
        public ?CarbonImmutable $expiresAt,
        public ?string $scope,
    ) {}

    /**
     * @param  array<string, mixed>  $response
     */
    public static function fromTokenResponse(array $response): self
    {
        $expiresIn = isset($response['expires_in']) ? (int) $response['expires_in'] : null;

        return new self(
            accessToken: (string) ($response['access_token'] ?? ''),
            refreshToken: isset($response['refresh_token']) ? (string) $response['refresh_token'] : null,
            expiresAt: $expiresIn !== null ? now()->addSeconds($expiresIn)->toImmutable() : null,
            scope: isset($response['scope']) ? (string) $response['scope'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_at' => $this->expiresAt,
            'scope' => $this->scope,
        ];
    }
}
