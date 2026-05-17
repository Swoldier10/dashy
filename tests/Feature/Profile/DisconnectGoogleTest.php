<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class DisconnectGoogleTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_disconnect_when_password_set(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'google_id' => 'google-abc',
        ]);
        $this->actingAs($user);

        Livewire::test('settings.integrations-section')
            ->call('disconnectGoogle')
            ->assertHasNoErrors();

        $this->assertNull($user->fresh()->google_id);
    }

    public function test_blocked_when_no_password_set(): void
    {
        $user = User::factory()->create([
            'password' => null,
            'google_id' => 'google-abc',
        ]);
        $this->actingAs($user);

        Livewire::test('settings.integrations-section')
            ->call('disconnectGoogle')
            ->assertHasErrors('google');

        $this->assertSame('google-abc', $user->fresh()->google_id);
    }
}
