<?php

namespace Tests\Unit\Domains\Chat\Actions;

use App\Domains\Chat\Actions\ListExpiredChatsAction;
use App\Domains\Chat\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListExpiredChatsActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_only_chats_with_updated_at_before_cutoff(): void
    {
        $user = User::factory()->create();
        $fresh = Chat::create(['user_id' => $user->id, 'title' => 'fresh']);
        $stale = Chat::create(['user_id' => $user->id, 'title' => 'stale']);
        Chat::query()->whereKey($stale->id)->update(['updated_at' => now()->subDays(11)]);

        $result = (new ListExpiredChatsAction)->execute(now()->subDays(10));

        $this->assertCount(1, $result);
        $this->assertSame($stale->id, $result->first()->id);
        $this->assertFalse($result->contains('id', $fresh->id));
    }

    public function test_returns_empty_collection_when_no_chats_are_expired(): void
    {
        $user = User::factory()->create();
        Chat::create(['user_id' => $user->id, 'title' => 'fresh']);

        $result = (new ListExpiredChatsAction)->execute(now()->subDays(10));

        $this->assertCount(0, $result);
    }

    public function test_treats_cutoff_as_strict_less_than(): void
    {
        $user = User::factory()->create();
        $atCutoff = Chat::create(['user_id' => $user->id, 'title' => 'edge']);
        $cutoff = now()->subDays(10);
        Chat::query()->whereKey($atCutoff->id)->update(['updated_at' => $cutoff]);

        $result = (new ListExpiredChatsAction)->execute($cutoff);

        $this->assertCount(0, $result, 'Chat updated exactly at the cutoff should not be expired.');
    }
}
