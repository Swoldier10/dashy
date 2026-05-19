<?php

namespace Tests\Unit\Domains\Chat\Services;

use App\Domains\Chat\Models\Chat;
use App\Domains\Chat\Services\UpdateChatStopStateService;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateChatStopStateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_stop_persists_timestamp_for_owner(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id]);

        app(UpdateChatStopStateService::class)->requestStop($user, $chat);

        $this->assertNotNull($chat->refresh()->stop_requested_at);
    }

    public function test_clear_stop_nulls_timestamp_for_owner(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id, 'stop_requested_at' => CarbonImmutable::now()]);

        app(UpdateChatStopStateService::class)->clearStop($user, $chat);

        $this->assertNull($chat->refresh()->stop_requested_at);
    }

    public function test_request_stop_rejects_non_owner(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $chat = Chat::create(['user_id' => $owner->id]);

        $this->expectException(ModelNotFoundException::class);

        app(UpdateChatStopStateService::class)->requestStop($intruder, $chat);
    }

    public function test_clear_stop_rejects_non_owner(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $chat = Chat::create(['user_id' => $owner->id, 'stop_requested_at' => CarbonImmutable::now()]);

        $this->expectException(ModelNotFoundException::class);

        app(UpdateChatStopStateService::class)->clearStop($intruder, $chat);
    }
}
