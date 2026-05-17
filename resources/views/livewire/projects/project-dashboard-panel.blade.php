<div class="flex flex-col gap-4" data-test="project-dashboard-panel">
    <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between">
        <x-dashy.tabs wire-model="scope" default-value="{{ $scope }}">
            <x-dashy.tab value="me">{{ __('Me') }}</x-dashy.tab>
            <x-dashy.tab value="team">{{ __('Team') }}</x-dashy.tab>
        </x-dashy.tabs>

        <div class="flex items-center justify-center gap-2 sm:justify-end">
            <x-dashy.button
                wire:click="previousMonth"
                variant="ghost"
                size="sm"
                icon="chevron-left"
                aria-label="{{ __('Previous month') }}"
            >
                <span class="sr-only">{{ __('Previous month') }}</span>
            </x-dashy.button>

            <span
                class="min-w-[8rem] text-center font-display text-sm"
                style="color: var(--ink);"
                data-test="project-dashboard-month-label"
            >{{ $this->monthLabel }}</span>

            <x-dashy.button
                wire:click="nextMonth"
                variant="ghost"
                size="sm"
                icon="chevron-right"
                aria-label="{{ __('Next month') }}"
            >
                <span class="sr-only">{{ __('Next month') }}</span>
            </x-dashy.button>

            @unless ($this->isCurrentMonth)
                <x-dashy.button
                    wire:click="goToCurrentMonth"
                    variant="ghost"
                    size="sm"
                >
                    {{ __('Today') }}
                </x-dashy.button>
            @endunless

            <x-dashy.button
                wire:click="exportMonth"
                wire:loading.attr="disabled"
                wire:target="exportMonth"
                variant="ghost"
                size="sm"
                icon="document-arrow-down"
                aria-label="{{ __('Excel-Export für ausgewählten Monat') }}"
                data-test="project-dashboard-export"
            >
                <span class="hidden md:inline">{{ __('Excel') }}</span>
                <span class="sr-only md:hidden">{{ __('Excel-Export für ausgewählten Monat') }}</span>
            </x-dashy.button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <x-dashy.card padding="md" class="lg:col-span-1">
            <div class="flex flex-col gap-2">
                <x-dashy.label>{{ __('Total time') }}</x-dashy.label>
                <span
                    class="font-display text-3xl sm:text-4xl"
                    style="color: var(--ink);"
                    data-test="project-dashboard-total-month"
                >
                    {{ \App\Domains\TimeTracking\Support\DurationParser::format($this->totalMonthSeconds) }}
                </span>
            </div>
        </x-dashy.card>

        <x-dashy.card padding="md" class="lg:col-span-2">
            <div class="flex flex-col gap-3">
                <x-dashy.label>{{ __('Daily hours') }}</x-dashy.label>
                <div wire:ignore class="relative h-64 w-full">
                    <canvas
                        x-data="dashyHoursChart(@js($this->chartLabels), @js($this->chartData))"
                        @chart-data-updated.window="update($event.detail.labels, $event.detail.values)"
                        data-test="project-dashboard-chart"
                    ></canvas>
                </div>
            </div>
        </x-dashy.card>
    </div>
</div>
