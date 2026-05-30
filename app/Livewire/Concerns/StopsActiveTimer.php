<?php

namespace App\Livewire\Concerns;

use App\Domains\TimeTracking\Services\StopTimerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Shared stop-timer handling for the timer pill and the task time panel.
 * Returns true when the active timer was stopped; on a validation failure
 * (e.g. nothing running) it toasts the message and returns false so the
 * caller can short-circuit its own success dispatch/toast.
 */
trait StopsActiveTimer
{
    protected function stopActiveTimer(StopTimerService $service): bool
    {
        try {
            $service->execute(Auth::user());
        } catch (ValidationException $e) {
            $this->toast('danger', collect($e->errors())->flatten()->first());

            return false;
        }

        return true;
    }
}
