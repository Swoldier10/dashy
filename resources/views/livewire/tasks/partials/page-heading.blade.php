@php
    /**
     * @var array<int, array{label:string, href:?string}> $breadcrumb
     * @var ?\App\Domains\Projects\Models\Project $project   // null on aggregator
     * @var string $title
     * @var ?string $subtitle
     * @var bool $showArchived
     */
    $breadcrumb = $breadcrumb ?? [];
    $project = $project ?? null;
    $title = $title ?? __('All tasks');
    $subtitle = $subtitle ?? null;
    $showArchived = $showArchived ?? false;
@endphp

<div class="flex flex-col gap-2" data-test="page-heading">
    {{-- Breadcrumb --}}
    @if (! empty($breadcrumb))
        <nav class="flex items-center gap-1.5 text-xs" style="color: var(--ink-dim);" aria-label="{{ __('Breadcrumb') }}">
            @foreach ($breadcrumb as $i => $crumb)
                @if ($i > 0)
                    <span aria-hidden="true">/</span>
                @endif
                @if ($crumb['href'] ?? null)
                    <a href="{{ $crumb['href'] }}" wire:navigate class="transition"
                       onmouseover="this.style.color='var(--ink)'" onmouseout="this.style.color='var(--ink-dim)'"
                    >{{ $crumb['label'] }}</a>
                @else
                    <span style="color: var(--ink-muted);">{{ $crumb['label'] }}</span>
                @endif
            @endforeach
        </nav>
    @endif

    <div class="flex items-start gap-3">
        {{-- Leading icon --}}
        <div class="shrink-0">
            @if ($project)
                <span class="flex size-9 items-center justify-center rounded-lg"
                      style="background-color: var(--surface-2);">
                    @include('livewire.tasks.partials.project-shape', ['project' => $project, 'size' => 'sm'])
                </span>
            @else
                <span class="flex size-9 items-center justify-center rounded-lg font-display text-lg"
                      style="background-color: var(--surface-2); color: var(--ink);">Σ</span>
            @endif
        </div>

        {{-- Title row above; description and action buttons share the row below. --}}
        <div class="min-w-0 flex-1">
            <h1 class="truncate font-display text-xl sm:text-2xl" style="color: var(--ink); line-height: 1.2;" data-test="page-heading-title">
                {{ $title }}
            </h1>

            <div class="mt-1 flex flex-wrap items-center gap-3">
                @if ($subtitle)
                    <p class="min-w-0 flex-1 truncate text-sm" style="color: var(--ink-muted);">{{ $subtitle }}</p>
                @else
                    <div class="flex-1"></div>
                @endif

                <div class="flex shrink-0 items-center gap-2" data-test="page-heading-actions">
                    <x-dashy.button
                        wire:click="toggleArchivedVisibility"
                        variant="ghost"
                        size="sm"
                        :icon="$showArchived ? 'eye-slash' : 'eye'"
                        data-test="tasks-toggle-archived"
                    >
                        {{ $showArchived ? __('Hide archived') : __('Show archived') }}
                    </x-dashy.button>

                    <x-dashy.button
                        wire:click="openCreateTask"
                        variant="cocoa"
                        size="sm"
                        icon="plus"
                        data-test="tasks-header-add"
                    >
                        {{ __('New task') }}
                    </x-dashy.button>
                </div>
            </div>
        </div>
    </div>
</div>
