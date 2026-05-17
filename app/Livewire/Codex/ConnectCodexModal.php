<?php

namespace App\Livewire\Codex;

use App\Domains\Codex\Exceptions\CodexAuthException;
use App\Domains\Codex\Services\CodexAuthService;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ConnectCodexModal extends Component
{
    use DispatchesDashyUi;

    public ?string $deviceAuthId = null;

    public ?string $userCode = null;

    public ?string $verificationUrl = null;

    public int $pollIntervalMs = 5000;

    public bool $isPolling = false;

    #[On('open-connect-codex')]
    public function start(CodexAuthService $service): void
    {
        $this->reset(['deviceAuthId', 'userCode', 'verificationUrl', 'pollIntervalMs', 'isPolling']);

        try {
            $code = $service->startDeviceCode();
        } catch (CodexAuthException $e) {
            $this->toast('danger', $e->getMessage());

            return;
        }

        $this->deviceAuthId = $code->deviceAuthId;
        $this->userCode = $code->userCode;
        $this->verificationUrl = $code->verificationUrl;
        $this->pollIntervalMs = max(2, $code->pollIntervalSeconds) * 1000;
        $this->isPolling = true;

        $this->openModal('connect-codex');
    }

    public function poll(CodexAuthService $service): void
    {
        if (! $this->isPolling || $this->deviceAuthId === null || $this->userCode === null) {
            return;
        }

        try {
            $connection = $service->pollForToken(Auth::user(), $this->deviceAuthId, $this->userCode);
        } catch (CodexAuthException $e) {
            $this->stop();
            $this->closeModal('connect-codex');
            $this->toast('danger', $e->getMessage());

            return;
        }

        if ($connection === null) {
            return; // still pending
        }

        $this->stop();
        $this->closeModal('connect-codex');
        $this->toast('success', __('Codex connected.'));
        $this->dispatch('codex-connected');
    }

    public function cancel(): void
    {
        $this->stop();
        $this->closeModal('connect-codex');
    }

    private function stop(): void
    {
        $this->isPolling = false;
        $this->deviceAuthId = null;
        $this->userCode = null;
        $this->verificationUrl = null;
    }

    public function render()
    {
        return view('livewire.codex.connect-codex-modal');
    }
}
