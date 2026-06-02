<?php

namespace Tests\Unit\Domains\Notifications\Actions;

use App\Domains\Notifications\Actions\CountUnreadNotificationsForUserAction;
use App\Domains\Notifications\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountUnreadNotificationsForUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_counts_only_the_users_unread_notifications(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Notification::factory()->count(2)->for($user, 'recipient')->create();
        Notification::factory()->read()->for($user, 'recipient')->create();
        Notification::factory()->for($other, 'recipient')->create();

        $this->assertSame(2, (new CountUnreadNotificationsForUserAction)->execute($user->id));
    }

    public function test_returns_zero_when_there_are_none(): void
    {
        $user = User::factory()->create();

        $this->assertSame(0, (new CountUnreadNotificationsForUserAction)->execute($user->id));
    }
}
