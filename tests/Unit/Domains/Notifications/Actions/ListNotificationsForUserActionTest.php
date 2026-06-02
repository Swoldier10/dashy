<?php

namespace Tests\Unit\Domains\Notifications\Actions;

use App\Domains\Notifications\Actions\ListNotificationsForUserAction;
use App\Domains\Notifications\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListNotificationsForUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_only_the_users_notifications_newest_first(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $old = Notification::factory()->for($user, 'recipient')->create(['created_at' => now()->subHour()]);
        $new = Notification::factory()->for($user, 'recipient')->create(['created_at' => now()]);
        Notification::factory()->for($other, 'recipient')->create();

        $result = (new ListNotificationsForUserAction)->execute($user->id);

        $this->assertSame([$new->id, $old->id], $result->pluck('id')->all());
    }

    public function test_respects_the_limit(): void
    {
        $user = User::factory()->create();
        Notification::factory()->count(5)->for($user, 'recipient')->create();

        $result = (new ListNotificationsForUserAction)->execute($user->id, limit: 3);

        $this->assertCount(3, $result);
    }

    public function test_unread_only_excludes_read_rows(): void
    {
        $user = User::factory()->create();
        $unread = Notification::factory()->for($user, 'recipient')->create();
        Notification::factory()->read()->for($user, 'recipient')->create();

        $result = (new ListNotificationsForUserAction)->execute($user->id, unreadOnly: true);

        $this->assertSame([$unread->id], $result->pluck('id')->all());
    }
}
