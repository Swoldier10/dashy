<?php

namespace App\Livewire\Concerns;

use App\Domains\Codex\Services\FindCodexConnectionForUserService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

/**
 * Shared Codex-connection state for Livewire components that surface whether
 * the user has linked their Codex account and which model is active.
 */
trait ResolvesCodexState
{
    #[Computed]
    public function isCodexConnected(): bool
    {
        return app(FindCodexConnectionForUserService::class)->execute(Auth::user()) !== null;
    }

    #[Computed]
    public function modelLabel(): string
    {
        return (string) config('services.codex.model');
    }
}
