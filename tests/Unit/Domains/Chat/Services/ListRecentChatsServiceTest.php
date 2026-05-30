<?php

namespace Tests\Unit\Domains\Chat\Services;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Services\ListRecentChatsService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListRecentChatsServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): ListRecentChatsService
    {
        return app(ListRecentChatsService::class);
    }

    public function test_caps_the_result_to_the_requested_limit(): void
    {
        $user = User::factory()->create();
        for ($i = 0; $i < 4; $i++) {
            Chat::create(['user_id' => $user->id, 'title' => "c{$i}"]);
        }

        $this->assertCount(2, $this->service()->execute($user, 2));
    }

    public function test_clamps_a_non_positive_limit_to_at_least_one(): void
    {
        $user = User::factory()->create();
        Chat::create(['user_id' => $user->id, 'title' => 'c']);
        Chat::create(['user_id' => $user->id, 'title' => 'c2']);

        $this->assertCount(1, $this->service()->execute($user, 0));
    }

    public function test_returns_only_the_actors_chats(): void
    {
        $user = User::factory()->create();
        Chat::create(['user_id' => $user->id, 'title' => 'mine']);
        Chat::create(['user_id' => User::factory()->create()->id, 'title' => 'theirs']);

        $chats = $this->service()->execute($user);

        $this->assertCount(1, $chats);
        $this->assertSame('mine', $chats->first()->title);
    }
}
