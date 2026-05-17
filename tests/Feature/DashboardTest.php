<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_visiting_dashboard_are_redirected_to_chat(): void
    {
        // /dashboard kept as a back-compat redirect to /chat after the
        // sidebar redesign moved primary nav out of an in-page tab.
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('dashboard'))->assertRedirect(route('chat'));
        $this->get(route('chat'))->assertOk();
    }
}
