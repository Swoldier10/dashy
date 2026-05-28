<?php

namespace Database\Factories;

use App\Domains\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GoogleCalendarConnection>
 */
class GoogleCalendarConnectionFactory extends Factory
{
    protected $model = GoogleCalendarConnection::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'access_token' => 'fake-access-token',
            'refresh_token' => 'fake-refresh-token',
            'expires_at' => now()->addHour(),
            'scope' => 'https://www.googleapis.com/auth/calendar.events',
            'account_email' => fake()->safeEmail(),
            'calendar_id' => 'primary',
            'sync_token' => null,
            'last_synced_at' => null,
            'last_sync_error' => null,
            'last_sync_error_at' => null,
        ];
    }
}
