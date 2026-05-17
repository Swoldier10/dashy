<?php

namespace Tests\Unit\Domains\Auth\Services;

use App\Domains\Auth\Services\DisconnectGoogleService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DisconnectGoogleServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): DisconnectGoogleService
    {
        return app(DisconnectGoogleService::class);
    }

    public function test_disconnects_when_password_exists(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'google_id' => 'google-abc',
            'avatar' => 'https://example.com/me.png',
        ]);

        $updated = $this->service()->execute($user);

        $this->assertNull($updated->google_id);
        $this->assertSame('https://example.com/me.png', $updated->avatar, 'Avatar should not be cleared.');
    }

    public function test_refuses_disconnect_without_password(): void
    {
        $user = User::factory()->create([
            'password' => null,
            'google_id' => 'google-abc',
        ]);

        try {
            $this->service()->execute($user);
            $this->fail('ValidationException not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('google', $e->errors());
        }

        $this->assertSame('google-abc', $user->fresh()->google_id);
    }
}
