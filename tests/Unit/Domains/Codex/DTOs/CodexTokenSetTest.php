<?php

namespace Tests\Unit\Domains\Codex\DTOs;

use App\Domains\Codex\DTOs\CodexTokenSet;
use Tests\TestCase;

class CodexTokenSetTest extends TestCase
{
    public function test_derives_expires_at_from_expires_in(): void
    {
        $set = CodexTokenSet::fromTokenResponse([
            'access_token' => 'a',
            'refresh_token' => 'r',
            'expires_in' => 3600,
            'scope' => 'chat',
        ]);

        $this->assertSame('a', $set->accessToken);
        $this->assertSame('r', $set->refreshToken);
        $this->assertSame('chat', $set->scope);
        $this->assertNotNull($set->expiresAt);
        $this->assertEqualsWithDelta(3600, now()->diffInSeconds($set->expiresAt), 5);
    }

    public function test_to_attributes_returns_expected_shape(): void
    {
        $set = CodexTokenSet::fromTokenResponse([
            'access_token' => 'a',
            'refresh_token' => 'r',
            'expires_in' => 60,
            'scope' => 'x',
        ]);

        $attrs = $set->toAttributes();

        $this->assertArrayHasKey('access_token', $attrs);
        $this->assertArrayHasKey('refresh_token', $attrs);
        $this->assertArrayHasKey('expires_at', $attrs);
        $this->assertArrayHasKey('scope', $attrs);
    }

    public function test_handles_missing_optional_fields(): void
    {
        $set = CodexTokenSet::fromTokenResponse(['access_token' => 'a']);

        $this->assertSame('a', $set->accessToken);
        $this->assertNull($set->refreshToken);
        $this->assertNull($set->expiresAt);
        $this->assertNull($set->scope);
    }
}
