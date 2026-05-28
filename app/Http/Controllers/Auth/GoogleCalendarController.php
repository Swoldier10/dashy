<?php

namespace App\Http\Controllers\Auth;

use App\Domains\GoogleCalendar\Exceptions\GoogleCalendarSyncException;
use App\Domains\GoogleCalendar\Services\ConnectGoogleCalendarService;
use App\Domains\GoogleCalendar\Services\HandleGoogleCalendarCallbackService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class GoogleCalendarController extends Controller
{
    public function redirect(ConnectGoogleCalendarService $service): RedirectResponse
    {
        return $service->execute();
    }

    public function callback(HandleGoogleCalendarCallbackService $service, Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            $service->execute($user);
        } catch (GoogleCalendarSyncException $e) {
            return redirect()
                ->route('dashboard')
                ->with('status', 'google-calendar-connect-failed')
                ->withErrors(['google_calendar' => $e->getMessage()]);
        }

        return redirect()
            ->route('dashboard')
            ->with('status', 'google-calendar-connected');
    }
}
