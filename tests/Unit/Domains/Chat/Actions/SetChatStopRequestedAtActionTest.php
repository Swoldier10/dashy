<?php

namespace Tests\Unit\Domains\Chat\Actions;

use App\Domains\Chat\Actions\SetChatStopRequestedAtAction;
use App\Domains\Chat\Models\Chat;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetChatStopRequestedAtActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_writes_timestamp(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);
        $when = CarbonImmutable::parse('2026-05-19 10:00:00');

        (new SetChatStopRequestedAtAction)->execute($chat, $when);

        $this->assertNotNull($chat->refresh()->stop_requested_at);
        $this->assertTrue($chat->stop_requested_at->equalTo($when));
    }

    public function test_clears_timestamp_when_null(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id, 'stop_requested_at' => CarbonImmutable::now()]);

        (new SetChatStopRequestedAtAction)->execute($chat, null);

        $this->assertNull($chat->refresh()->stop_requested_at);
    }
}
