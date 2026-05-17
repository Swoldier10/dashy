<?php

namespace Tests\Feature;

use App\Domains\Chat\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BottomTabBarTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_on_chat_home(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('chat'));

        $response->assertOk();
        $response->assertSee('data-test="bottom-tab-bar"', escape: false);
        $response->assertSee('data-test="bottom-tab-chat-home"', escape: false);
        $response->assertSee('data-test="bottom-tab-calendar"', escape: false);
        $response->assertSee('data-test="bottom-tab-tasks"', escape: false);
        $response->assertSee('data-test="bottom-tab-settings"', escape: false);
    }

    public function test_chat_home_tab_is_active_on_chat_route(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('chat'));

        $response->assertOk();
        $this->assertStringContainsString(
            'data-test="bottom-tab-chat-home" data-active="true"',
            $response->getContent(),
        );
    }

    public function test_chat_home_tab_is_active_on_chat_show_route(): void
    {
        $user = User::factory()->create();
        $chat = Chat::create(['user_id' => $user->id, 'title' => 'Hello']);
        $this->actingAs($user);

        $response = $this->get(route('chat.show', $chat));

        $response->assertOk();
        $this->assertStringContainsString(
            'data-test="bottom-tab-chat-home" data-active="true"',
            $response->getContent(),
        );
    }

    public function test_calendar_tab_is_active_on_calendar_route(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('calendar'));

        $response->assertOk();
        $this->assertStringContainsString(
            'data-test="bottom-tab-calendar" data-active="true"',
            $response->getContent(),
        );
    }

    public function test_tasks_tab_is_active_on_tasks_route(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('tasks'));

        $response->assertOk();
        $this->assertStringContainsString(
            'data-test="bottom-tab-tasks" data-active="true"',
            $response->getContent(),
        );
    }

    public function test_bar_is_absent_for_guests(): void
    {
        $response = $this->followingRedirects()->get(route('home'));

        // Guests land on the login page, which isn't wrapped in the app shell.
        $response->assertDontSee('data-test="bottom-tab-bar"', escape: false);
    }
}
