<?php

namespace Tests\Feature\Auth;

use App\Domains\Auth\Enums\Salutation;
use App\Domains\Teams\Enums\TeamRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register_and_columns_are_persisted_separately(): void
    {
        $response = $this->post(route('register.store'), [
            'salutation' => 'mr',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => '1',
        ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertSame(Salutation::Mr, $user->salutation);
        $this->assertSame('John', $user->first_name);
        $this->assertSame('Doe', $user->last_name);
        $this->assertSame('Mr John Doe', $user->name);
    }

    public function test_registration_creates_personal_team_owned_by_new_user(): void
    {
        $this->post(route('register.store'), [
            'salutation' => 'mr',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'team@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => '1',
        ]);

        $user = User::where('email', 'team@example.com')->firstOrFail();
        $personal = $user->teams()->where('teams.personal_team', true)->first();

        $this->assertNotNull($personal);
        $this->assertSame("John's Team", $personal->name);
        $this->assertSame(
            TeamRole::Owner->value,
            $personal->members()->whereKey($user->id)->first()->pivot->role->value,
        );
    }

    public function test_invalid_salutation_is_rejected(): void
    {
        $response = $this->post(route('register.store'), [
            'salutation' => 'lord',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors('salutation');
        $this->assertGuest();
    }

    public function test_missing_terms_is_rejected(): void
    {
        $response = $this->post(route('register.store'), [
            'salutation' => 'ms',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('terms');
        $this->assertGuest();
    }

    public function test_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->post(route('register.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'taken@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_password_mismatch_is_rejected(): void
    {
        $response = $this->post(route('register.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_first_name_over_80_chars_is_rejected(): void
    {
        $response = $this->post(route('register.store'), [
            'first_name' => str_repeat('a', 81),
            'last_name' => 'Doe',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors('first_name');
        $this->assertGuest();
    }
}
