<?php

namespace App\Livewire\TimeTracking;

use App\Domains\TimeTracking\Actions\FindActiveTimerForUserAction;
use App\Domains\TimeTracking\Models\TimeEntry;
use App\Domains\TimeTracking\Services\StopTimerService;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class RunningTimerPill extends Component
{
    use DispatchesDashyUi;

    #[Computed]
    public function activeEntry(): ?TimeEntry
    {
        if (! Auth::check()) {
            return null;
        }

        return app(FindActiveTimerForUserAction::class)
            ->execute(Auth::user())
            ?->load('task.project');
    }

    public function stop(StopTimerService $service): void
    {
        try {
            $service->execute(Auth::user());
        } catch (ValidationException $e) {
            $this->toast('danger', collect($e->errors())->flatten()->first());

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
