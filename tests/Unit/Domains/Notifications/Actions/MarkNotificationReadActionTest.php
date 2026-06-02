<?php

namespace Tests\Unit\Domains\Notifications\Actions;

use App\Domains\Notifications\Actions\MarkNotificationReadAction;
use App\Domains\Notifications\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkNotificationReadActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_marks_an_own_unread_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->for($user, 'recipient')->create();

        $updated = (new MarkNotificationReadAction)->execute($user->id, $notification->id);

        $this->assertSame(1, $updated);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_cannot_mark_another_users_notification(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $notification = Notification::factory()->for($other, 'recipient')->create();

        $updated = (new MarkNotificationReadAction)->execute($user->id, $notification->id);

        $this->assertSame(0, $updated);
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_already_read_rows_are_left_untouched(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->read()->for($user, 'recipient')->create();
        $readAt = $notification->read_at;

        $updated = (new MarkNotificationReadAction)->execute($user->id, $notification->id);

        $this->assertSame(0, $updated);
        $this->assertTrue($notification->fresh()->read_at->equalTo($readAt));
    }
}
