<?php

namespace Tests\Feature\Settings;

use App\Domains\Preferences\Models\UserPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WorkingHoursSectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_mount_renders_all_seven_days_off_when_no_preference_exists(): void
    {
        $this->actingAs(User::factory()->create());

        $component = Livewire::test('settings.working-hours-section');

        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            $component->assertSeeHtml('data-test="working-hours-day-'.$day.'"');
        }

        $component->assertSet('hours.monday', []);
        $component->assertSet('hours.sunday', []);
    }

    public function test_mount_hydrates_ranges_from_existing_preference(): void
    {
        $user = User::factory()->create();
        UserPreference::create([
            'user_id' => $user->id,
            'key' => 'working_hours',
            'value' => [
                'monday' => [
                    ['start' => '09:00', 'end' => '12:00'],
                    ['start' => '13:00', 'end' => '17:00'],
                ],
                'friday' => [['start' => '08:00', 'end' => '14:00']],
            ],
        ]);

        $this->actingAs($user);

        Livewire::test('settings.working-hours-section')
            ->assertSet('hours.monday', [
                ['start' => '09:00', 'end' => '12:00'],
                ['start' => '13:00', 'end' => '17:00'],
            ])
            ->assertSet('hours.friday', [['start' => '08:00', 'end' => '14:00']])
            ->assertSet('hours.tuesday', []);
    }

    public function test_add_range_appends_default_range(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('settings.working-hours-section')
            ->call('addRange', 'monday')
            ->assertSet('hours.monday', [['start' => '09:00', 'end' => '17:00']]);
    }

    public function test_add_range_ignores_unknown_day(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('settings.working-hours-section')
            ->call('addRange', 'funday')
            ->assertSet('hours.monday', []);
    }

    public function test_remove_range_drops_entry_and_reindexes(): void
    {
        $user = User::factory()->create();
        UserPreference::create([
            'user_id' => $user->id,
            'key' => 'working_hours',
            'value' => [
                'monday' => [
                    ['start' => '09:00', 'end' => '12:00'],
                    ['start' => '13:00', 'end' => '17:00'],
                ],
            ],
        ]);

        $this->actingAs($user);

        Livewire::test('settings.working-hours-section')
            ->call('removeRange', 'monday', 0)
            ->assertSet('hours.monday', [['start' => '13:00', 'end' => '17:00']]);
    }

    public function test_save_persists_hours_and_fires_success_toast(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('settings.working-hours-section')
            ->set('hours.monday', [['start' => '09:00', 'end' => '17:00']])
            ->call('save')
            ->assertDispatched('dashy-toast', variant: 'success');

        $pref = UserPreference::query()
            ->where('user_id', $user->id)
            ->where('key', 'working_hours')
            ->first();

        $this->assertNotNull($pref);
        $this->assertSame([['start' => '09:00', 'end' => '17:00']], $pref->value['monday']);
    }

    public function test_save_surfaces_validation_error_inline(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('settings.working-hours-section')
            ->set('hours.monday', [['start' => '17:00', 'end' => '09:00']])
            ->call('save')
            ->assertHasErrors('hours.monday.0.end');
    }
}
