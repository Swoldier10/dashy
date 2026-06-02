<?php

namespace Tests\Feature\Notifications;

use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Notifications\Models\Notification;
use App\Domains\Teams\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_is_lazy_until_the_panel_opens(): void
    {
        $user = User::factory()->create();
        Notification::factory()->for($user, 'recipient')->create();
        $this->actingAs($user);

        $component = Livewire::test('notifications.panel');
        $this->assertCount(0, $component->instance()->rows);

        $component->dispatch('notifications-panel:open')
            ->assertSet('open', true)
            ->assertDispatched('dashy-modal:open', name: 'notifications-panel');

        $this->assertCount(1, $component->instance()->rows);
    }

    public function test_lists_presenter_rendered_rows_for_the_current_user_only(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Notification::factory()->for($user, 'recipient')->withData([
            'task_id' => 5, 'task_name' => 'Fix the build', 'actor_name' => 'Anna', 'project_name' => 'Website',
        ])->create();
        Notification::factory()->for($other, 'recipient')->create();
        $this->actingAs($user);

        Livewire::test('notifications.panel')
            ->dispatch('notifications-panel:open')
            ->assertSee('Anna assigned you to "Fix the build"')
            ->assertSee('Website');
    }

    public function test_empty_state_when_there_are_no_notifications(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('notifications.panel')
            ->dispatch('notifications-panel:open')
            ->assertSee('No notifications');
    }

    public function test_opening_a_task_notification_marks_it_read_and_navigates(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->for($user, 'recipient')->withData([
            'task_id' => 42, 'task_name' => 'Fix', 'actor_name' => 'Anna',
        ])->create();
        $this->actingAs($user);

        Livewire::test('notifications.panel')
            ->dispatch('notifications-panel:open')
            ->call('openNotification', $notification->id)
            ->assertDispatched('notifications:read')
            ->assertRedirect(route('tasks').'?task=42');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_team_notifications_navigate_to_the_team_page(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $notification = Notification::factory()
            ->for($user, 'recipient')
            ->forTeam($team)
            ->ofType(NotificationType::MemberJoined)
            ->withData(['team_id' => $team->id, 'team_name' => $team->name, 'member_name' => 'Mia'])
            ->create();
        $this->actingAs($user);

        Livewire::test('notifications.panel')
            ->dispatch('notifications-panel:open')
            ->call('openNotification', $notification->id)
            ->assertRedirect(route('teams.show', $team->id));
    }

    public function test_notification_without_a_subject_id_still_marks_read_without_redirect(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->for($user, 'recipient')->withData([
            'task_name' => 'Ghost task', 'actor_name' => 'Anna',
        ])->create();
        $this->actingAs($user);

        Livewire::test('notifications.panel')
            ->dispatch('notifications-panel:open')
            ->call('openNotification', $notification->id)
            ->assertNoRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_cannot_open_another_users_notification(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $foreign = Notification::factory()->for($other, 'recipient')->create();
        $this->actingAs($user);

        Livewire::test('notifications.panel')
            ->dispatch('notifications-panel:open')
            ->call('openNotification', $foreign->id)
            ->assertNoRedirect();

        $this->assertNull($foreign->fresh()->read_at);
    }

    public function test_mark_all_read_clears_unread_and_notifies_the_bell(): void
    {
        $user = User::factory()->create();
        Notification::factory()->count(3)->for($user, 'recipient')->create();
        $this->actingAs($user);

        Livewire::test('notifications.panel')
            ->dispatch('notifications-panel:open')
            ->call('markAllRead')
            ->assertDispatched('notifications:read');

        $this->assertSame(
            0,
            Notification::query()->where('user_id', $user->id)->whereNull('read_at')->count(),
        );
    }

    public function test_closing_the_panel_resets_the_lazy_gate(): void
    {
        $user = User::factory()->create();
        Notification::factory()->for($user, 'recipient')->create();
        $this->actingAs($user);

        $component = Livewire::test('notifications.panel')
            ->dispatch('notifications-panel:open')
            ->assertSet('open', true)
            ->call('closePanel')
            ->assertSet('open', false);

        $this->assertCount(0, $component->instance()->rows);
    }

    public function test_unread_rows_carry_the_data_read_attribute(): void
    {
        $user = User::factory()->create();
        $unread = Notification::factory()->for($user, 'recipient')->create();
        $read = Notification::factory()->read()->for($user, 'recipient')->create();
        $this->actingAs($user);

        Livewire::test('notifications.panel')
            ->dispatch('notifications-panel:open')
            ->assertSeeHtml('data-test="notification-row-'.$unread->id.'"')
            ->assertSeeHtml('data-read="false"')
            ->assertSeeHtml('data-test="notification-row-'.$read->id.'"')
            ->assertSeeHtml('data-read="true"');
    }
}
