<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])
        ->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])
        ->name('auth.google.callback');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Back-compat — old links and tests using route('dashboard') still resolve.
    Route::redirect('dashboard', 'chat')->name('dashboard');

    // Primary surfaces (mirrored as sidebar nav items)
    Route::view('chat', 'chat')->name('chat');
    Route::view('chat/{chat}', 'chat')->name('chat.show')->whereNumber('chat');
    Route::view('calendar', 'calendar')->name('calendar');
    Route::livewire('tasks', 'pages::tasks.index')->name('tasks');
    Route::livewire('tasks/{project}', 'pages::tasks.show')
        ->whereNumber('project')
        ->name('tasks.show');

    Route::livewire('teams', 'pages::teams')->name('teams.index');
    Route::livewire('teams/{team}', 'pages::teams.show')
        ->whereNumber('team')
        ->name('teams.show');
});
