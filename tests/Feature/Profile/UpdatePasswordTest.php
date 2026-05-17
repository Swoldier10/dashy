<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UpdatePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $this->actingAs($user);

        Livewire::test('settings.security-section')
            ->set('current_password', 'password')
            ->set('password', 'NewSecret123!')
            ->set('password_confirmation', 'NewSecret123!')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('NewSecret123!', $user->refresh()->password));
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $this->actingAs($user);

        Livewire::test('settings.security-section')
            ->set('current_password', 'wrong-password')
            ->set('password', 'NewSecret123!')
            ->set('password_confirmation', 'NewSecret123!')
            ->call('updatePassword')
            ->assertHasErrors('current_password');
    }

    public function test_mismatched_confirmation_is_rejected(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $this->actingAs($user);

        Livewire::test('settings.security-section')
            ->set('current_password', 'password')
            ->set('password', 'NewSecret123!')
            ->set('password_confirmation', 'Different123!')
            ->call('updatePassword')
            ->assertHasErrors('password');
    }

    public function test_google_only_user_can_set_a_password(): void
    {
        $user = User::factory()->create(['password' => null]);
        $this->actingAs($user);

        Livewire::test('settings.security-section')
            ->set('password', 'NewSecret123!')
            ->set('password_confirmation', 'NewSecret123!')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('NewSecret123!', $user->refresh()->password));
    }
}
