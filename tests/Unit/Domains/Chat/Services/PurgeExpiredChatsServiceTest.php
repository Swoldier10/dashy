<?php

namespace Tests\Unit\Domains\Chat\Services;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Models\Message;
use App\Domains\Chat\Services\PurgeExpiredChatsService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PurgeExpiredChatsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_chats_inactive_for_10_or_more_days(): void
    {
        $user = User::factory()->create();
        $stale = Chat::create(['user_id' => $user->id, 'title' => 'stale']);
        Chat::query()->whereKey($stale->id)->update(['updated_at' => now()->subDays(11)]);

        $count = app(PurgeExpiredChatsService::class)->execute();

        $this->assertSame(1, $count);
        $this->assertSame(0, Chat::count());
    }

    public function test_keeps_chats_active_within_window(): void
    {
        $user = User::factory()->create();
        $fresh = Chat::create(['user_id' => $user->id, 'title' => 'fresh']);
        Chat::query()->whereKey($fresh->id)->update(['updated_at' => now()->subDays(3)]);

        $count = app(PurgeExpiredChatsService::class)->execute();

        $this->assertSame(0, $count);
        $this->assertSame(1, Chat::count());
    }

    public function test_cleans_up_attachment_files_for_purged_chats(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('chat-attachments/1/1/a.png', 'png-bytes');
        Storage::disk('public')->put('chat-attachments/1/1/b.png', 'png-bytes');

        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id, 'title' => 'stale']);
        Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'msg',
            'attachments' => [
                ['path' => 'chat-attachments/1/1/a.png'],
                ['path' => 'chat-attachments/1/1/b.png'],
            ],
        ]);
        Chat::query()->whereKey($chat->id)->update(['updated_at' => now()->subDays(15)]);

        app(PurgeExpiredChatsService::class)->execute();

        Storage::disk('public')->assertMissing('chat-attachments/1/1/a.png');
        Storage::disk('public')->assertMissing('chat-attachments/1/1/b.png');
    }

    public function test_cascades_to_messages(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id, 'title' => 'stale']);
        Message::create(['chat_id' => $chat->id, 'role' => 'user', 'content' => 'a']);
        Message::create(['chat_id' => $chat->id, 'role' => 'assistant', 'content' => 'b']);
        Chat::query()->whereKey($chat->id)->update(['updated_at' => now()->subDays(20)]);

        app(PurgeExpiredChatsService::class)->execute();

        $this->assertSame(0, Chat::count());
        $this->assertSame(0, Message::count());
    }

    public function test_respects_custom_days_argument(): void
    {
        $user = User::factory()->create();
        $five = Chat::create(['user_id' => $user->id, 'title' => 'five']);
        $forty = Chat::create(['user_id' => $user->id, 'title' => 'forty']);
        Chat::query()->whereKey($five->id)->update(['updated_at' => now()->subDays(5)]);
        Chat::query()->whereKey($forty->id)->update(['updated_at' => now()->subDays(40)]);

        $count = app(PurgeExpiredChatsService::class)->execute(30);

        $this->assertSame(1, $count);
        $this->assertNotNull(Chat::find($five->id));
        $this->assertNull(Chat::find($forty->id));
    }

    public function test_returns_zero_when_nothing_is_expired(): void
    {
        $user = User::factory()->create();
        Chat::create(['user_id' => $user->id, 'title' => 'fresh']);

        $count = app(PurgeExpiredChatsService::class)->execute();

        $this->assertSame(0, $count);
    }
}
