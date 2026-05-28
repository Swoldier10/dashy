<?php

namespace App\Domains\GoogleCalendar\Services;

use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;

/**
 * Builds the Google OAuth redirect used by the integrations panel to grant
 * Calendar access. Distinct from the sign-in flow in SocialAuthService — that
 * one logs the user in and creates a personal team, neither of which we want
 * for a "this signed-in user is also linking their calendar" flow.
 */
final class ConnectGoogleCalendarService
{
    public const SCOPE_CALENDAR_EVENTS = 'https://www.googleapis.com/auth/calendar.events';

    public function execute(): RedirectResponse
    {
        /** @var Provider $driver */
        $driver = Socialite::driver('google');

        if ($driver instanceof GoogleProvider) {
            $driver->scopes(['openid', 'email', 'profile', self::SCOPE_CALENDAR_EVENTS]);
        }

        return $driver
            ->redirectUrl(route('google-calendar.callback'))
            ->with([
                'access_type' => 'offline',
                'prompt' => 'consent',
                'include_granted_scopes' => 'true',
            ])
            ->redirect();
    }
}
