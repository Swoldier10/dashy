<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SettingsModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_modal_defaults_to_profile_section(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('settings-modal')
            ->assertSet('section', 'profile');
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function sectionKeys(): array
    {
        return [
            'profile' => ['profile'],
            'appearance' => ['appearance'],
            'security' => ['security'],
            'integrations' => ['integrations'],
            'memory' => ['memory'],
        ];
    }

    #[DataProvider('sectionKeys')]
    public function test_set_section_switches_to_each_valid_section(string $section): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('settings-modal')
            ->call('setSection', $section)
            ->assertSet('section', $section);
    }

    public function test_unknown_section_is_ignored(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('settings-modal')
            ->call('setSection', 'not-a-real-section')
            ->assertSet('section', 'profile');
    }

    public function test_modal_markup_contains_logout_form(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('settings-modal')
            ->assertSeeHtml('action="'.route('logout').'"')
            ->assertSee('Log out');
    }

    public function test_modal_lists_all_five_section_tabs(): void
    {
        $this->actingAs(User::factory()->create());

        $component = Livewire::test('settings-modal');

        foreach (['profile', 'appearance', 'security', 'integrations', 'memory'] as $key) {
            $component->assertSeeHtml('data-test="settings-tab-'.$key.'"');
        }
    }
}
