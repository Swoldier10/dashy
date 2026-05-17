<?php

namespace Tests\Feature\Profile;

use App\Domains\Auth\Enums\Salutation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UpdateProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_section_renders(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('settings.profile-section')
            ->assertOk()
            ->assertSet('email', $user->email);
    }

    public function test_profile_information_persists_separated_columns(): void
    {
        $user = User::factory()->create([
            'salutation' => 'mr',
            'first_name' => 'Old',
            'last_name' => 'Name',
            'name' => 'Mr Old Name',
            'email' => 'old@example.com',
        ]);

        $this->actingAs($user);

        Livewire::test('settings.profile-section')
            ->set('salutation', 'ms')
            ->set('first_name', 'New')
            ->set('last_name', 'Name')
            ->set('email', 'new@example.com')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertSame(Salutation::Ms, $user->salutation);
        $this->assertSame('New', $user->first_name);
        $this->assertSame('Name', $user->last_name);
        $this->assertSame('Ms New Name', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_unchanged_when_email_unchanged(): void
    {
        $user = User::factory()->create([
            'salutation' => 'mr',
            'first_name' => 'Pat',
            'last_name' => 'Doe',
            'email' => 'pat@example.com',
        ]);

        $this->actingAs($user);

        Livewire::test('settings.profile-section')
            ->set('first_name', 'Patricia')
            ->set('last_name', 'Doe')
            ->set('email', 'pat@example.com')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_invalid_salutation_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('settings.profile-section')
            ->set('salutation', 'lord')
            ->set('first_name', 'A')
            ->set('last_name', 'B')
            ->set('email', $user->email)
            ->call('updateProfile')
            ->assertHasErrors('salutation');
    }

    public function test_missing_first_name_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('settings.profile-section')
            ->set('salutation', null)
            ->set('first_name', '')
            ->set('last_name', 'B')
            ->set('email', $user->email)
            ->call('updateProfile')
            ->assertHasErrors('first_name');
    }
}
