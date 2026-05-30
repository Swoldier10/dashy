<?php

namespace Tests\Unit\Domains\Chat\Actions;

use App\Domains\Chat\Actions\FindChatAction;
use App\Domains\Chat\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindChatActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_chat_when_found(): void
    {
        $user = User::factory()->create();
        $chat = Chat::query()->create(['user_id' => $user->id, 'title' => 'x']);

        $found = (new FindChatAction)->execute($chat->id);

        $this->assertNotNull($found);
        $this->assertTrue($chat->is($found));
    }

    public function test_returns_null_when_missing(): void
    {
        $this->assertNull((new FindChatAction)->execute(999_999));
    }
}
