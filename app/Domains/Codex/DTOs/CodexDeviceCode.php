<?php

namespace App\Domains\Codex\DTOs;

final readonly class CodexDeviceCode
{
    public function __construct(
        public string $deviceAuthId,
        public string $userCode,
        public string $verificationUrl,
        public int $pollIntervalSeconds,
    ) {}
}
