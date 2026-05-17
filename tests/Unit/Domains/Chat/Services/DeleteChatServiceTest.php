<?php

namespace Tests\Unit\Domains\Chat\Services;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Services\DeleteChatService;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteChatServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_owned_chat(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);

        app(DeleteChatService::class)->execute($user, $chat->id);

        $this->assertSame(0, Chat::count());
    }

    public function test_throws_when_chat_belongs_to_other_user(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $chat = Chat::create(['user_id' => $owner->id]);

        $this->expectException(ModelNotFoundException::class);

        app(DeleteChatService::class)->execute($intruder, $chat->id);
    }

    public function test_throws_when_chat_does_not_exist(): void
    {
        $user = User::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        app(DeleteChatService::class)->execute($user, 9999);
    }
}
