<?php

namespace Tests\Unit\Domains\Notifications\Actions;

use App\Domains\Notifications\Actions\MarkAllNotificationsReadForUserAction;
use App\Domains\Notifications\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkAllNotificationsReadForUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_marks_all_of_the_users_unread_notifications(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Notification::factory()->count(3)->for($user, 'recipient')->create();
        $foreign = Notification::factory()->for($other, 'recipient')->create();

        $updated = (new MarkAllNotificationsReadForUserAction)->execute($user->id);

        $this->assertSame(3, $updated);
        $this->assertSame(0, Notification::query()->where('user_id', $user->id)->whereNull('read_at')->count());
        $this->assertNull($foreign->fresh()->read_at);
    }

    public function test_returns_zero_when_nothing_is_unread(): void
    {
        $user = User::factory()->create();
        Notification::factory()->read()->for($user, 'recipient')->create();

        $this->assertSame(0, (new MarkAllNotificationsReadForUserAction)->execute($user->id));
    }
}
