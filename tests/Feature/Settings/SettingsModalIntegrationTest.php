<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsModalIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_modal_is_mounted_globally_in_app_shell(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('chat'))
            ->assertOk()
            ->assertSee('data-modal-name="settings"', escape: false);
    }

    public function test_sidebar_user_card_opens_settings_modal(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('chat'))
            ->assertOk()
            ->assertSeeHtml('data-test="sidebar-user-menu"')
            ->assertSeeHtml("\$store.modals.open('settings')");
    }

    public function test_bottom_tab_settings_opens_settings_modal(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('chat'))
            ->assertOk()
            ->assertSeeHtml('data-test="bottom-tab-settings"')
            ->assertSeeHtml("\$store.modals.open('settings')");
    }
}
