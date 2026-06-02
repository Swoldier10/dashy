<?php

namespace Tests\Unit\Domains\Notifications\Services;

use App\Domains\Notifications\Services\GetNotificationPreferencesService;
use App\Domains\Notifications\Services\UpdateNotificationPreferencesService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateNotificationPreferencesServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_persists_a_valid_channel_map(): void
    {
        $user = User::factory()->create();

        $normalized = app(UpdateNotificationPreferencesService::class)->execute($user, [
            'task_assigned' => ['email' => false, 'app' => true],
        ]);

        $this->assertSame(['task_assigned' => ['email' => false, 'app' => true]], $normalized);
        $this->assertSame(
            ['email' => false, 'app' => true],
            app(GetNotificationPreferencesService::class)->execute($user->id)['task_assigned'],
        );
    }

    public function test_rejects_unknown_notification_types(): void
    {
        $user = User::factory()->create();

        try {
            app(UpdateNotificationPreferencesService::class)->execute($user, [
                'task_assigned' => ['email' => true, 'app' => true],
                'made_up' => ['email' => true, 'app' => true],
            ]);
            $this->fail('Expected a ValidationException.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('preferences', $e->errors());
        }
    }

    public function test_rejects_non_boolean_channel_values_with_keyed_errors(): void
    {
        $user = User::factory()->create();

        try {
            app(UpdateNotificationPreferencesService::class)->execute($user, [
                'task_assigned' => ['email' => 'yes', 'app' => true],
            ]);
            $this->fail('Expected a ValidationException.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('preferences.task_assigned.email', $e->errors());
        }
    }

    public function test_rejects_channels_that_are_not_an_array(): void
    {
        $user = User::factory()->create();

        try {
            app(UpdateNotificationPreferencesService::class)->execute($user, [
                'task_assigned' => 'all',
            ]);
            $this->fail('Expected a ValidationException.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('preferences.task_assigned', $e->errors());
        }
    }

    public function test_nothing_is_persisted_when_validation_fails(): void
    {
        $user = User::factory()->create();

        try {
            app(UpdateNotificationPreferencesService::class)->execute($user, [
                'task_assigned' => ['email' => false, 'app' => true],
                'made_up' => ['email' => true, 'app' => true],
            ]);
            $this->fail('Expected a ValidationException.');
        } catch (ValidationException) {
            $this->assertDatabaseMissing('user_preferences', [
                'user_id' => $user->id,
                'key' => GetNotificationPreferencesService::PREFERENCE_KEY,
            ]);
        }
    }
}
