<?php

use App\Domains\Preferences\Services\ForgetMemoryService;
use App\Domains\Preferences\Services\ListMemoriesService;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    use DispatchesDashyUi;

    public function forget(string $key): void
    {
        try {
            app(ForgetMemoryService::class)->execute(Auth::user(), 'user', $key);
            $this->toast('success', __('Memory removed.'));
        } catch (\Throwable $e) {
            $this->toast('danger', __('Could not remove memory').': '.$e->getMessage());
        }

        unset($this->memories);
    }

    /**
     * @return array<int, array{key: string, fact: string, created_at: ?string}>
     */
    #[Computed]
    public function memories(): array
    {
        $rows = app(ListMemoriesService::class)->execute(Auth::user(), 'user');

        return $rows->map(function ($pref): array {
            $value = is_array($pref->value) ? $pref->value : [];

            return [
                'key' => (string) $pref->key,
                'fact' => (string) ($value['fact'] ?? ''),
                'created_at' => $value['created_at'] ?? null,
            ];
        })->values()->all();
    }
}; ?>

<div>
    <section class="dashy-settings-section">
        <div class="dashy-settings-section-head">
            <h3>{{ __('Memory') }}</h3>
            <p>{{ __('Facts the assistant has saved about you. These are surfaced into every future chat session.') }}</p>
        </div>

        @if (count($this->memories) === 0)
            <div class="dashy-settings-row" data-test="memory-empty">
                <div class="dashy-settings-row-label">
                    <span class="row-label-text">{{ __('No memories yet') }}</span>
                    <span class="row-label-desc">{{ __('When you tell the assistant to remember something, it will appear here.') }}</span>
                </div>
            </div>
        @else
            <ul class="flex flex-col" data-test="memory-list">
                @foreach ($this->memories as $memory)
                    <li class="dashy-settings-row" wire:key="memory-{{ $memory['key'] }}">
                        <div class="dashy-settings-row-label min-w-0">
                            <span class="row-label-text">{{ $memory['fact'] }}</span>
                            <span class="row-label-desc">
                                <span class="font-mono">{{ $memory['key'] }}</span>
                                @if (! empty($memory['created_at']))
                                    · {{ \Illuminate\Support\Carbon::parse($memory['created_at'])->diffForHumans() }}
                                @endif
                            </span>
                        </div>
                        <div class="dashy-settings-row-value flex justify-start sm:justify-end">
                            <button
                                type="button"
                                wire:click="forget('{{ $memory['key'] }}')"
                                class="dashy-btn dashy-btn--sm"
                                style="color: var(--state-error); border-color: var(--border-mid); background-color: transparent;"
                                data-test="forget-{{ $memory['key'] }}"
                            >
                                {{ __('Forget') }}
                            </button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>
