<?php

namespace Tests\Unit\Domains\Auth\Services;

use App\Domains\Auth\Actions\CreateUserAction;
use App\Domains\Auth\Enums\Salutation;
use App\Domains\Auth\Services\RegisterUserService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class RegisterUserServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): RegisterUserService
    {
        return app(RegisterUserService::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function validInput(array $overrides = []): array
    {
        return array_merge([
            'salutation' => 'mr',
            'first_name' => 'Lina',
            'last_name' => 'Marsh',
            'email' => 'lina@example.com',
            'password' => 'CorrectHorse9!',
            'password_confirmation' => 'CorrectHorse9!',
            'terms' => '1',
        ], $overrides);
    }

    public function test_persists_separated_columns_and_composes_legacy_name(): void
    {
        $user = $this->service()->create($this->validInput());

        $this->assertSame(Salutation::Mr, $user->salutation);
        $this->assertSame('Lina', $user->first_name);
        $this->assertSame('Marsh', $user->last_name);
        $this->assertSame('Mr Lina Marsh', $user->name);
        $this->assertSame('lina@example.com', $user->email);
        $this->assertTrue(Hash::check('CorrectHorse9!', $user->password));
    }

    public function test_composes_legacy_name_without_salutation_when_null(): void
    {
        $user = $this->service()->create($this->validInput(['salutation' => null]));

        $this->assertNull($user->salutation);
        $this->assertSame('Lina Marsh', $user->name);
    }

    public function test_rejects_invalid_salutation(): void
    {
        $this->expectException(ValidationException::class);

        $this->service()->create($this->validInput(['salutation' => 'lord']));
    }

    public function test_rejects_missing_terms(): void
    {
        try {
            $this->service()->create($this->validInput(['terms' => null]));
            $this->fail('ValidationException not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('terms', $e->errors());
        }
    }

    public function test_rejects_unaccepted_terms(): void
    {
        try {
            $this->service()->create($this->validInput(['terms' => '0']));
            $this->fail('ValidationException not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('terms', $e->errors());
        }
    }

    public function test_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->expectException(ValidationException::class);

        $this->service()->create($this->validInput(['email' => 'taken@example.com']));
    }

    public function test_rejects_password_mismatch(): void
    {
        try {
            $this->service()->create($this->validInput(['password_confirmation' => 'different']));
            $this->fail('ValidationException not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('password', $e->errors());
        }
    }

    public function test_rejects_oversized_first_name(): void
    {
        try {
            $this->service()->create($this->validInput(['first_name' => str_repeat('a', 81)]));
            $this->fail('ValidationException not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('first_name', $e->errors());
        }
    }

    public function test_rolls_back_transaction_when_action_throws(): void
    {
        $mock = Mockery::mock(CreateUserAction::class);
        $mock->shouldReceive('execute')->andThrow(new \RuntimeException('Simulated DB failure.'));
        $this->app->instance(CreateUserAction::class, $mock);

        try {
            $this->service()->create($this->validInput(['email' => 'rollback@example.com']));
            $this->fail('Exception not propagated.');
        } catch (\RuntimeException) {
            // expected
        }

        $this->assertDatabaseMissing('users', ['email' => 'rollback@example.com']);
    }
}
