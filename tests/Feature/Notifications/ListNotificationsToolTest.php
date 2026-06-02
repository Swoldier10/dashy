<?php

namespace Tests\Feature\Notifications;

use App\Domains\Chat\Ai\Services\AiToolRegistry;
use App\Domains\Chat\Ai\Tools\ListNotificationsTool;
use App\Domains\Notifications\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListNotificationsToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_registered_in_the_tool_registry(): void
    {
        $this->assertNotNull(app(AiToolRegistry::class)->find('list_notifications'));
    }

    public function test_lists_the_users_notifications_with_presenter_titles(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Notification::factory()->for($user, 'recipient')->withData([
            'task_id' => 5, 'task_name' => 'Fix the build', 'actor_name' => 'Anna',
        ])->create();
        Notification::factory()->for($other, 'recipient')->create();

        $result = app(ListNotificationsTool::class)->execute($user, []);

        $this->assertSame(1, $result['count']);
        $this->assertSame('Anna assigned you to "Fix the build"', $result['notifications'][0]['title']);
        $this->assertSame('task_assigned', $result['notifications'][0]['type']);
        $this->assertFalse($result['notifications'][0]['is_read']);
    }

    public function test_unread_only_filters_read_rows(): void
    {
        $user = User::factory()->create();
        Notification::factory()->for($user, 'recipient')->create();
        Notification::factory()->read()->for($user, 'recipient')->create();

        $result = app(ListNotificationsTool::class)->execute($user, ['unread_only' => true]);

        $this->assertSame(1, $result['count']);
    }

    public function test_validate_clamps_the_limit_and_coerces_flags(): void
    {
        $user = User::factory()->create();
        $tool = app(ListNotificationsTool::class);

        $result = $tool->validate($user, ['limit' => 500, 'unread_only' => true]);

        $this->assertTrue($result->valid);
        $this->assertSame(50, $result->normalized['limit']);
        $this->assertTrue($result->normalized['unread_only']);
    }
}
