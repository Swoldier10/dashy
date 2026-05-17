<?php

namespace Tests\Unit\Domains\Chat\Enums;

use App\Domains\Chat\Enums\MessageRole;
use PHPUnit\Framework\TestCase;

class MessageRoleTest extends TestCase
{
    public function test_cases_have_expected_values(): void
    {
        $this->assertSame('user', MessageRole::User->value);
        $this->assertSame('assistant', MessageRole::Assistant->value);
        $this->assertSame('system', MessageRole::System->value);
    }
}
