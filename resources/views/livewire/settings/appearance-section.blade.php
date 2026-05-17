<?php

use Livewire\Component;

new class extends Component
{
    //
}; ?>

<div>
    <section class="dashy-settings-section">
        <div class="dashy-settings-section-head">
            <h3>{{ __('Appearance') }}</h3>
            <p>{{ __('How Dashy looks on this device.') }}</p>
        </div>

        <div class="dashy-settings-row">
            <div class="dashy-settings-row-label">
                <span class="row-label-text">{{ __('Theme') }}</span>
                <span class="row-label-desc">{{ __('Light, dark, or follow the system setting.') }}</span>
            </div>
            <div class="dashy-settings-row-value">
                <x-dashy.radio-group
                    name="appearance"
                    variant="segmented"
                    x-data="{
                        value: localStorage.getItem('appearance') ?? 'system',
                        apply(v) {
                            localStorage.setItem('appearance', v);
                            const dark = v === 'dark' || (v === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                            document.documentElement.classList.toggle('dark', dark);
                        },
                    }"
                    x-init="apply(value)"
                    @change="apply(value)"
                >
                    <x-dashy.radio value="light" icon="sun" x-model="value">{{ __('Light') }}</x-dashy.radio>
                    <x-dashy.radio value="dark" icon="moon" x-model="value">{{ __('Dark') }}</x-dashy.radio>
                    <x-dashy.radio value="system" icon="computer-desktop" x-model="value">{{ __('System') }}</x-dashy.radio>
                </x-dashy.radio-group>
            </div>
        </div>
    </section>
</div>
