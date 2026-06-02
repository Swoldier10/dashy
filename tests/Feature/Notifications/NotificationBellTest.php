<?php

namespace Tests\Feature\Notifications;

use App\Domains\Notifications\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_for_both_variants(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('notifications.bell', ['variant' => 'sidebar'])->assertOk();
        Livewire::test('notifications.bell', ['variant' => 'mobile'])->assertOk();
    }

    public function test_variants_render_distinct_markup(): void
    {
        $this->actingAs(User::factory()->create());

        // Sidebar variant carries the nav-row label; mobile is icon-only.
        Livewire::test('notifications.bell', ['variant' => 'sidebar'])
            ->assertSee('Notifications');

        Livewire::test('notifications.bell', ['variant' => 'mobile'])
            ->assertDontSee('Notifications');
    }

    public function test_badge_shows_the_unread_count_for_the_current_user_only(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Notification::factory()->count(3)->for($user, 'recipient')->create();
        Notification::factory()->read()->for($user, 'recipient')->create();
        Notification::factory()->count(5)->for($other, 'recipient')->create();
        $this->actingAs($user);

        Livewire::test('notifications.bell')
            ->assertSeeHtml('data-test="notifications-badge"')
            ->assertSee('3');
    }

    public function test_badge_is_hidden_at_zero_unread(): void
    {
        $user = User::factory()->create();
        Notification::factory()->read()->for($user, 'recipient')->create();
        $this->actingAs($user);

        Livewire::test('notifications.bell')
            ->assertDontSeeHtml('data-test="notifications-badge"');
    }

    public function test_badge_caps_at_nine_plus(): void
    {
        $user = User::factory()->create();
        Notification::factory()->count(12)->for($user, 'recipient')->create();
        $this->actingAs($user);

        Livewire::test('notifications.bell')->assertSee('9+');
    }

    public function test_polls_only_while_visible(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('notifications.bell')
            ->assertSeeHtml('wire:poll.visible.30s');
    }

    public function test_open_dispatches_the_panel_event(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('notifications.bell')
            ->call('open')
            ->assertDispatched('notifications-panel:open');
    }

    public function test_refreshes_immediately_when_notifications_are_read(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->for($user, 'recipient')->create();
        $this->actingAs($user);

        $component = Livewire::test('notifications.bell')->assertSee('1');

        $notification->update(['read_at' => now()]);

        $component->dispatch('notifications:read')
            ->assertDontSeeHtml('data-test="notifications-badge"');
    }
}
