<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_section_renders_change_password_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('settings.security-section')
            ->assertSee('Change password')
            ->assertSee('Current password')
            ->assertSee('New password')
            ->assertSee('Confirm new password');
    }

    public function test_security_section_renders_danger_zone(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('settings.security-section')
            ->assertSee('Danger zone')
            ->assertSee('Delete account');
    }

    public function test_security_section_offers_set_password_for_google_only_user(): void
    {
        $user = User::factory()->create(['password' => null]);

        $this->actingAs($user);

        Livewire::test('settings.security-section')
            ->assertSee('Set password')
            ->assertDontSee('Current password');
    }
}
