<?php

namespace Tests\Feature\Chat;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Services\SendMessageService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SendMessageRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_throttles_a_user_who_sends_too_many_messages_per_minute(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id, 'title' => 'rl']);
        $service = app(SendMessageService::class);

        // The cap is 30/minute; 30 sends succeed.
        for ($i = 0; $i < 30; $i++) {
            $service->saveUserMessage($chat, "msg {$i}");
        }

        $this->expectException(ValidationException::class);
        $service->saveUserMessage($chat, 'one too many');
    }

    public function test_separate_users_have_independent_limits(): void
    {
        $service = app(SendMessageService::class);
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $chatA = Chat::create(['user_id' => $userA->id, 'title' => 'a']);
        $chatB = Chat::create(['user_id' => $userB->id, 'title' => 'b']);

        for ($i = 0; $i < 30; $i++) {
            $service->saveUserMessage($chatA, "a {$i}");
        }

        // User B is unaffected by user A hitting the cap.
        $message = $service->saveUserMessage($chatB, 'hello from B');
        $this->assertNotNull($message->id);
    }
}
