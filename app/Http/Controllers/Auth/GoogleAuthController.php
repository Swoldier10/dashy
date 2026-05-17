<?php

namespace App\Http\Controllers\Auth;

use App\Domains\Auth\Exceptions\SocialAuthException;
use App\Domains\Auth\Services\SocialAuthService;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class GoogleAuthController extends Controller
{
    public function redirect(SocialAuthService $service): RedirectResponse
    {
        return $service->redirectToGoogle();
    }

    public function callback(SocialAuthService $service, Request $request): RedirectResponse
    {
        try {
            $result = $service->handleGoogleCallback();
        } catch (SocialAuthException) {
            return redirect()->route('login')->withErrors([
                'email' => __("We couldn't complete your Google sign-in. Please try again."),
            ]);
        }

        if ($result->isNewUser) {
            event(new Registered($result->user));
        }

        Auth::login($result->user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
