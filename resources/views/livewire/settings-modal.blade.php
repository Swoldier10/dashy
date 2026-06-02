<?php

use Livewire\Component;

new class extends Component
{
    public string $section = 'profile';

    /**
     * @return array<string, array{label: string, icon: string}>
     */
    public function sections(): array
    {
        return [
            'profile' => ['label' => __('Profile'), 'icon' => 'user-circle'],
            'working-hours' => ['label' => __('Working Hours'), 'icon' => 'clock'],
            'notifications' => ['label' => __('Notifications'), 'icon' => 'bell'],
            'appearance' => ['label' => __('Appearance'), 'icon' => 'sun'],
            'security' => ['label' => __('Security'), 'icon' => 'lock-closed'],
            'integrations' => ['label' => __('Integrations'), 'icon' => 'bolt'],
            'memory' => ['label' => __('Memory'), 'icon' => 'bookmark'],
        ];
    }

    public function setSection(string $name): void
    {
        if (! array_key_exists($name, $this->sections())) {
            return;
        }

        $this->section = $name;
    }
}; ?>

<div>
    <x-dashy.modal
        name="settings"
        size="xl"
        style="padding: 0; overflow: hidden;"
        data-test="settings-modal"
    >
        <div
            class="dashy-settings-modal grid grid-cols-1 md:grid-cols-[220px_minmax(0,1fr)]"
            style="height: min(640px, calc(100dvh - 64px));"
        >
            {{-- Left nav (becomes horizontal tab strip on mobile) --}}
            <nav
                class="flex flex-col gap-1 border-b p-3 md:border-b-0 md:border-r md:p-4"
                style="border-color: var(--border); background-color: var(--surface-2);"
                aria-label="{{ __('Settings sections') }}"
            >
                <p
                    class="hidden px-2 pb-2 pt-1 text-[11px] font-semibold uppercase tracking-wider md:block"
                    style="color: var(--ink-dim);"
                >
                    {{ __('Settings') }}
                </p>

                <ul
                    role="tablist"
                    aria-orientation="vertical"
                    class="flex gap-1 overflow-x-auto md:flex-col md:overflow-visible"
                >
                    @foreach ($this->sections() as $key => $meta)
                        @php
                            $isActive = $section === $key;
                        @endphp
                        <li class="shrink-0 md:shrink">
                            <button
                                type="button"
                                role="tab"
                                wire:click="setSection('{{ $key }}')"
                                aria-selected="{{ $isActive ? 'true' : 'false' }}"
                                aria-controls="settings-section-pane"
                                data-test="settings-tab-{{ $key }}"
                                data-active="{{ $isActive ? 'true' : 'false' }}"
                                class="flex min-h-[40px] w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium transition"
                                style="
                                    background-color: {{ $isActive ? 'var(--surface)' : 'transparent' }};
                                    color: {{ $isActive ? 'var(--ink)' : 'var(--ink-muted)' }};
                                    box-shadow: {{ $isActive ? '0 1px 0 0 rgba(var(--ink-rgb), 0.04)' : 'none' }};
                                "
                                @if (! $isActive)
                                    onmouseover="this.style.color='var(--ink)';"
                                    onmouseout="this.style.color='var(--ink-muted)';"
                                @endif
                            >
                                <x-dashy.icon :name="$meta['icon']" class="size-4" />
                                <span>{{ $meta['label'] }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>

                {{-- Log out (bottom on desktop, hidden inside nav on mobile — duplicated below content) --}}
                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    class="mt-auto hidden md:block"
                >
                    @csrf
                    <button
                        type="submit"
                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium transition"
                        style="color: var(--state-error); background-color: transparent;"
                        onmouseover="this.style.backgroundColor='var(--surface)'"
                        onmouseout="this.style.backgroundColor='transparent'"
                        data-test="settings-logout-button"
                    >
                        <x-dashy.icon name="arrow-right-start-on-rectangle" class="size-4" />
                        <span>{{ __('Log out') }}</span>
                    </button>
                </form>
            </nav>

            {{-- Right pane --}}
            <div class="flex min-h-0 flex-col">
                <header
                    class="flex items-center justify-between gap-3 border-b px-5 py-4 md:px-6"
                    style="border-color: var(--border);"
                >
                    <h2 class="font-display text-lg" style="color: var(--ink);">
                        {{ $this->sections()[$section]['label'] }}
                    </h2>
                    <button
                        type="button"
                        x-on:click="$store.modals.close('settings')"
                        class="flex size-9 items-center justify-center rounded-full transition"
                        style="color: var(--ink-muted); background-color: transparent;"
                        onmouseover="this.style.backgroundColor='var(--surface-2)';this.style.color='var(--ink)'"
                        onmouseout="this.style.backgroundColor='transparent';this.style.color='var(--ink-muted)'"
                        aria-label="{{ __('Close settings') }}"
                        data-test="settings-close-button"
                    >
                        <x-dashy.icon name="x-mark" class="size-5" />
                    </button>
                </header>

                <div
                    id="settings-section-pane"
                    role="tabpanel"
                    class="dashy-settings-pane flex-1 overflow-y-auto px-5 py-5 md:px-6"
                >
                    @switch($section)
                        @case('profile')
                            <livewire:settings.profile-section :key="'settings-profile'" />
                            @break
                        @case('working-hours')
                            <livewire:settings.working-hours-section :key="'settings-working-hours'" />
                            @break
                        @case('notifications')
                            <livewire:settings.notifications-section :key="'settings-notifications'" />
                            @break
                        @case('appearance')
                            <livewire:settings.appearance-section :key="'settings-appearance'" />
                            @break
                        @case('security')
                            <livewire:settings.security-section :key="'settings-security'" />
                            @break
                        @case('integrations')
                            <livewire:settings.integrations-section :key="'settings-integrations'" />
                            @break
                        @case('memory')
                            <livewire:settings.memory-section :key="'settings-memory'" />
                            @break
                    @endswitch
                </div>

                {{-- Mobile-only log out (nav's log out is desktop-only) --}}
                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    class="border-t px-5 py-3 md:hidden"
                    style="border-color: var(--border);"
                >
                    @csrf
                    <button
                        type="submit"
                        class="flex min-h-[44px] w-full items-center justify-center gap-2 rounded-lg text-sm font-medium"
                        style="color: var(--state-error); background-color: transparent;"
                        data-test="settings-logout-button-mobile"
                    >
                        <x-dashy.icon name="arrow-right-start-on-rectangle" class="size-4" />
                        <span>{{ __('Log out') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </x-dashy.modal>
</div>
