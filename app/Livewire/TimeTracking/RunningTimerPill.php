<?php

namespace App\Livewire\TimeTracking;

use App\Domains\TimeTracking\Models\TimeEntry;
use App\Domains\TimeTracking\Services\FindActiveTimerForUserService;
use App\Domains\TimeTracking\Services\StopTimerService;
use App\Livewire\Concerns\StopsActiveTimer;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class RunningTimerPill extends Component
{
    use DispatchesDashyUi;
    use StopsActiveTimer;

    #[Computed]
    public function activeEntry(): ?TimeEntry
    {
        if (! Auth::check()) {
            return null;
        }

        // The service returns the entry with task.project already eager-loaded
        // (see FindActiveTimerForUserAction) so the component issues no query.
        return app(FindActiveTimerForUserService::class)->execute(Auth::user());
    }

    public function stop(StopTimerService $service): void
    {
        if (! $this->stopActiveTimer($service)) {
            return;
        }

        unset($this->activeEntry);
        $this->dispatch('time-entries-updated');
        $this->toast('success', __('Timer stopped.'));
    }

    #[On('time-entries-updated')]
    public function refresh(): void
    {
        unset($this->activeEntry);
    }

    public function render()
    {
        return view('livewire.time-tracking.running-timer-pill');
    }
}
