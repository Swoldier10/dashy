<?php

namespace Tests\Unit\Domains\Auth\Services;

use App\Domains\Auth\Actions\UpdateUserAction;
use App\Domains\Auth\Enums\Salutation;
use App\Domains\Auth\Services\UpdateProfileInformationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class UpdateProfileInformationServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): UpdateProfileInformationService
    {
        return app(UpdateProfileInformationService::class);
    }

    private function user(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'salutation' => 'mr',
            'first_name' => 'Old',
            'last_name' => 'Name',
            'name' => 'Mr Old Name',
            'email' => 'old@example.com',
        ], $overrides));
    }

    public function test_persists_separated_columns_and_composes_legacy_name(): void
    {
        $user = $this->user();

        $updated = $this->service()->execute($user, [
            'salutation' => 'ms',
            'first_name' => 'New',
            'last_name' => 'Name',
            'email' => 'old@example.com',
        ]);

        $this->assertSame(Salutation::Ms, $updated->salutation);
        $this->assertSame('New', $updated->first_name);
        $this->assertSame('Name', $updated->last_name);
        $this->assertSame('Ms New Name', $updated->name);
    }

    public function test_clears_email_verified_at_when_email_changes(): void
    {
        $user = $this->user(['email_verified_at' => now()]);

        $updated = $this->service()->execute($user, [
            'salutation' => 'mr',
            'first_name' => 'Old',
            'last_name' => 'Name',
            'email' => 'changed@example.com',
        ]);

        $this->assertNull($updated->email_verified_at);
    }

    public function test_keeps_email_verified_at_when_email_unchanged(): void
    {
        $verifiedAt = now()->startOfSecond();
        $user = $this->user(['email_verified_at' => $verifiedAt]);

        $updated = $this->service()->execute($user, [
            'salutation' => 'mr',
            'first_name' => 'Old',
            'last_name' => 'Name',
            'email' => 'old@example.com',
        ]);

        $this->assertNotNull($updated->email_verified_at);
        $this->assertTrue($updated->email_verified_at->equalTo($verifiedAt));
    }

    public function test_rejects_invalid_salutation(): void
    {
        $this->expectException(ValidationException::class);

        $this->service()->execute($this->user(), [
            'salutation' => 'lord',
            'first_name' => 'A',
            'last_name' => 'B',
            'email' => 'old@example.com',
        ]);
    }

    public function test_rejects_missing_first_name(): void
    {
        try {
            $this->service()->execute($this->user(), [
                'salutation' => null,
                'first_name' => '',
                'last_name' => 'B',
                'email' => 'old@example.com',
            ]);
            $this->fail('ValidationException not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('first_name', $e->errors());
        }
    }

    public function test_rejects_oversize_first_name(): void
    {
        try {
            $this->service()->execute($this->user(), [
                'salutation' => null,
                'first_name' => str_repeat('a', 81),
                'last_name' => 'B',
                'email' => 'old@example.com',
            ]);
            $this->fail('ValidationException not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('first_name', $e->errors());
        }
    }

    public function test_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->expectException(ValidationException::class);

        $this->service()->execute($this->user(), [
            'salutation' => null,
            'first_name' => 'A',
            'last_name' => 'B',
            'email' => 'taken@example.com',
        ]);
    }

    public function test_rolls_back_when_action_throws(): void
    {
        $user = $this->user();

        $mock = Mockery::mock(UpdateUserAction::class);
        $mock->shouldReceive('execute')->andThrow(new \RuntimeException('Simulated DB failure.'));
        $this->app->instance(UpdateUserAction::class, $mock);

        try {
            $this->service()->execute($user, [
                'salutation' => null,
                'first_name' => 'New',
                'last_name' => 'Name',
                'email' => 'still-old@example.com',
            ]);
            $this->fail('Exception not propagated.');
        } catch (\RuntimeException) {
            // expected
        }

        $this->assertDatabaseMissing('users', ['email' => 'still-old@example.com']);
        $this->assertSame('old@example.com', $user->fresh()->email);
    }
}
