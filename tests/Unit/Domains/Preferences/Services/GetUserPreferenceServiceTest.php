<?php

namespace Tests\Unit\Domains\Preferences\Services;

use App\Domains\Preferences\Models\UserPreference;
use App\Domains\Preferences\Services\GetUserPreferenceService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetUserPreferenceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_the_stored_value(): void
    {
        $user = User::factory()->create();
        UserPreference::create(['user_id' => $user->id, 'key' => 'lang', 'value' => ['code' => 'de']]);

        $value = app(GetUserPreferenceService::class)->execute($user->id, 'lang');

        $this->assertSame(['code' => 'de'], $value);
    }

    public function test_returns_null_when_the_key_is_missing(): void
    {
        $user = User::factory()->create();

        $this->assertNull(app(GetUserPreferenceService::class)->execute($user->id, 'missing'));
    }
}
