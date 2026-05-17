<?php

namespace App\Domains\Chat\Ai\DTOs;

final class AiToolValidationResult
{
    /**
     * @param  array<int, string>  $errors
     * @param  array<string, mixed>  $normalized
     */
    public function __construct(
        public bool $valid,
        public array $errors = [],
        public array $normalized = [],
    ) {}

    /**
     * @param  array<string, mixed>  $normalized
     */
    public static function ok(array $normalized): self
    {
        return new self(true, [], $normalized);
    }

    /**
     * @param  array<int, string>  $errors
     */
    public static function fail(array $errors): self
    {
        return new self(false, $errors, []);
    }
}
