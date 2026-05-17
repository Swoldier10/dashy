<?php

namespace Tests\Unit\Domains\Chat\Services;

use App\Domains\Chat\Enums\MessageRole;
use App\Domains\Chat\Services\CreateChatService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateChatServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_chat_and_first_message(): void
    {
        $user = User::factory()->create();

        $chat = app(CreateChatService::class)->execute($user, 'Hello there, this is my first message.');

        $this->assertSame('Hello there, this is my first message.', $chat->title);
        $this->assertCount(1, $chat->messages);
        $this->assertSame(MessageRole::User, $chat->messages->first()->role);
    }

    public function test_truncates_long_first_message_for_title(): void
    {
        $user = User::factory()->create();
        $long = str_repeat('a', 200);

        $chat = app(CreateChatService::class)->execute($user, $long);

        $this->assertLessThanOrEqual(60, mb_strlen($chat->title));
    }

    public function test_rejects_empty_message(): void
    {
        $user = User::factory()->create();

        $this->expectException(ValidationException::class);

        app(CreateChatService::class)->execute($user, '');
    }

    public function test_rejects_too_long_message(): void
    {
        $user = User::factory()->create();

        $this->expectException(ValidationException::class);

        app(CreateChatService::class)->execute($user, str_repeat('a', 8001));
    }
}
