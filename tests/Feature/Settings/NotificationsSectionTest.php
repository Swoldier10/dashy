<?php

namespace Tests\Feature\Settings;

use App\Domains\Notifications\Enums\NotificationType;
use App\Domains\Notifications\Services\GetNotificationPreferencesService;
use App\Domains\Notifications\Services\UpdateNotificationPreferencesService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationsSectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_the_full_matrix_grouped_by_category(): void
    {
        $this->actingAs(User::factory()->create());

        $component = Livewire::test('settings.notifications-section')
            ->assertOk()
            ->assertSeeHtml('data-test="notifications-matrix"')
            ->assertSee('Tasks')
            ->assertSee('Teams')
            ->assertSee('Calendar');

        foreach (NotificationType::cases() as $type) {
            $component->assertSeeHtml('data-test="notif-row-'.$type->value.'"');
        }
    }

    public function test_mount_shows_enum_defaults_when_nothing_is_stored(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('settings.notifications-section')
            ->assertSet('prefs.task_assigned.email', true)
            ->assertSet('prefs.task_created_in_project.app', false);
    }

    public function test_mount_hydrates_previously_saved_preferences(): void
    {
        $user = User::factory()->create();
        app(UpdateNotificationPreferencesService::class)->execute($user, [
            'task_assigned' => ['email' => false, 'app' => true],
        ]);
        $this->actingAs($user);

        Livewire::test('settings.notifications-section')
            ->assertSet('prefs.task_assigned.email', false)
            ->assertSet('prefs.task_assigned.app', true);
    }

    public function test_toggling_a_cell_auto_saves_and_toasts(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('settings.notifications-section')
            ->set('prefs.task_assigned.email', false)
            ->assertDispatched('dashy-toast', variant: 'success');

        $this->assertFalse(
            app(GetNotificationPreferencesService::class)->execute($user->id)['task_assigned']['email'],
        );
    }

    public function test_channels_persist_independently(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('settings.notifications-section')
            ->set('prefs.task_overdue.email', false);

        $stored = app(GetNotificationPreferencesService::class)->execute($user->id)['task_overdue'];
        $this->assertFalse($stored['email']);
        $this->assertTrue($stored['app']);
    }

    public function test_persists_across_remount(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('settings.notifications-section')
            ->set('prefs.member_joined.app', false);

        Livewire::test('settings.notifications-section')
            ->assertSet('prefs.member_joined.app', false);
    }

    public function test_tampered_unknown_keys_are_ignored_and_not_persisted(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('settings.notifications-section')
            ->set('prefs.made_up_type.email', true)
            ->assertNotDispatched('dashy-toast');

        $this->assertDatabaseMissing('user_preferences', [
            'user_id' => $user->id,
            'key' => GetNotificationPreferencesService::PREFERENCE_KEY,
        ]);
    }
}
