<?php

namespace Tests\Unit\Domains\Auth\DTOs;

use App\Domains\Auth\DTOs\SocialAuthResult;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class SocialAuthResultTest extends TestCase
{
    public function test_carries_user_and_is_new_user_flag(): void
    {
        $user = new User(['email' => 'a@example.com']);

        $result = new SocialAuthResult($user, isNewUser: true);

        $this->assertSame($user, $result->user);
        $this->assertTrue($result->isNewUser);
    }
}
