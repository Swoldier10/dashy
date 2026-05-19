<?php

namespace Tests\Feature\Chat;

use App\Domains\Chat\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurgeExpiredChatsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_purges_expired_chats_and_reports_count(): void
    {
        $user = User::factory()->create();
        $stale = Chat::create(['user_id' => $user->id, 'title' => 'stale']);
        Chat::create(['user_id' => $user->id, 'title' => 'fresh']);
        Chat::query()->whereKey($stale->id)->update(['updated_at' => now()->subDays(11)]);

        $this->artisan('chats:purge-expired')
            ->expectsOutputToContain('Purged 1 expired chat(s)')
            ->assertExitCode(0);

        $this->assertSame(1, Chat::count());
    }

    public function test_honors_the_days_option(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id, 'title' => 'five']);
        Chat::query()->whereKey($chat->id)->update(['updated_at' => now()->subDays(5)]);

        $this->artisan('chats:purge-expired', ['--days' => 30])
            ->assertExitCode(0);

        $this->assertSame(1, Chat::count(), 'A 5-day-old chat should survive a 30-day cutoff.');

        $this->artisan('chats:purge-expired', ['--days' => 3])
            ->assertExitCode(0);

        $this->assertSame(0, Chat::count(), 'A 5-day-old chat should be purged when cutoff is 3 days.');
    }

    public function test_rejects_negative_days_value(): void
    {
        $this->artisan('chats:purge-expired', ['--days' => -1])
            ->assertExitCode(2);
    }
}
