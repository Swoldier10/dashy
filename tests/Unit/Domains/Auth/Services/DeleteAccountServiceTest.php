<?php

namespace Tests\Unit\Domains\Auth\Services;

use App\Domains\Auth\Services\DeleteAccountService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DeleteAccountServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): DeleteAccountService
    {
        return app(DeleteAccountService::class);
    }

    public function test_validates_when_password_correct(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $this->actingAs($user);

        $this->service()->validateInputs($user, ['password' => 'password']);

        $this->addToAssertionCount(1);
    }

    public function test_rejects_wrong_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $this->actingAs($user);

        try {
            $this->service()->validateInputs($user, ['password' => 'wrong-password']);
            $this->fail('ValidationException not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('password', $e->errors());
        }
    }

    public function test_google_only_user_validates_with_typed_confirmation(): void
    {
        $user = User::factory()->create(['password' => null]);

        $this->service()->validateInputs($user, ['confirmation' => 'DELETE']);

        $this->addToAssertionCount(1);
    }

    public function test_google_only_user_rejected_with_wrong_confirmation(): void
    {
        $user = User::factory()->create(['password' => null]);

        try {
            $this->service()->validateInputs($user, ['confirmation' => 'delete']);
            $this->fail('ValidationException not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('confirmation', $e->errors());
        }
    }

    public function test_delete_removes_user(): void
    {
        $user = User::factory()->create();

        $this->service()->delete($user);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
