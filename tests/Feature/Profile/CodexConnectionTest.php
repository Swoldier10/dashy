<?php

namespace Tests\Feature\Profile;

use App\Domains\Codex\Models\CodexConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CodexConnectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_integrations_section_shows_link_button_when_not_connected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test('settings.integrations-section')
            ->assertSee('Codex')
            ->assertSee('Not linked');
    }

    public function test_integrations_section_shows_disconnect_when_connected(): void
    {
        $user = User::factory()->create();
        CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
        ]);

        $this->actingAs($user);

        Livewire::test('settings.integrations-section')
            ->assertSee('Linked');
    }

    public function test_disconnect_removes_connection(): void
    {
        $user = User::factory()->create();
        CodexConnection::create([
            'user_id' => $user->id,
            'access_token' => 'a',
        ]);
        $this->actingAs($user);

        Livewire::test('settings.integrations-section')
            ->call('disconnectCodex')
            ->assertHasNoErrors();

        $this->assertSame(0, CodexConnection::count());
    }
}
