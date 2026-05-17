<?php

namespace Tests\Unit\Domains\Auth\Services;

use App\Domains\Auth\Services\UpdatePasswordService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdatePasswordServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): UpdatePasswordService
    {
        return app(UpdatePasswordService::class);
    }

    public function test_updates_password_when_current_password_correct(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $this->actingAs($user);

        $this->service()->execute($user, [
            'current_password' => 'password',
            'password' => 'NewSecret123!',
            'password_confirmation' => 'NewSecret123!',
        ]);

        $this->assertTrue(Hash::check('NewSecret123!', $user->refresh()->password));
    }

    public function test_rejects_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $this->actingAs($user);

        try {
            $this->service()->execute($user, [
                'current_password' => 'wrong-password',
                'password' => 'NewSecret123!',
                'password_confirmation' => 'NewSecret123!',
            ]);
            $this->fail('ValidationException not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('current_password', $e->errors());
        }
    }

    public function test_rejects_mismatched_confirmation(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $this->actingAs($user);

        try {
            $this->service()->execute($user, [
                'current_password' => 'password',
                'password' => 'NewSecret123!',
                'password_confirmation' => 'Different123!',
            ]);
            $this->fail('ValidationException not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('password', $e->errors());
        }
    }

    public function test_google_only_user_can_set_password_without_current(): void
    {
        $user = User::factory()->create(['password' => null]);
        $this->actingAs($user);

        $this->service()->execute($user, [
            'password' => 'NewSecret123!',
            'password_confirmation' => 'NewSecret123!',
        ]);

        $this->assertTrue(Hash::check('NewSecret123!', $user->refresh()->password));
    }
}
