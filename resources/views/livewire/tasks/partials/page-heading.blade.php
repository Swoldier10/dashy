@php
    /**
     * @var array<int, array{label:string, href:?string}> $breadcrumb
     * @var ?\App\Domains\Projects\Models\Project $project   // null on aggregator
     * @var string $title
     * @var bool $showArchived
     */
    $breadcrumb = $breadcrumb ?? [];
    $project = $project ?? null;
    $title = $title ?? __('All tasks');
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

    {{-- Title row: leading icon + title only; toolbar moved underneath. --}}
    <div class="flex items-center gap-3">
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
        <h1 class="min-w-0 flex-1 truncate font-display text-xl sm:text-2xl" style="color: var(--ink); line-height: 1.2;" data-test="page-heading-title">
            {{ $title }}
        </h1>
    </div>

    {{-- Toolbar — full width, sidebar-coloured strip with outlined icon buttons. --}}
    <div
        class="flex w-full items-center justify-end gap-1 rounded-lg border px-2 py-1"
        style="background-color: var(--surface-2); border-color: var(--border); box-shadow: 0 1px 2px rgba(var(--ink-rgb), 0.04);"
        data-test="page-heading-toolbar"
    >
        <x-dashy.tooltip :text="$showArchived ? __('Hide archived') : __('Show archived')" position="bottom" align="end">
            <x-dashy.button
                wire:click="toggleArchivedVisibility"
                variant="filled"
                size="sm"
                iconOnly
                :icon="$showArchived ? 'eye-slash' : 'eye'"
                :aria-label="$showArchived ? __('Hide archived') : __('Show archived')"
                data-test="tasks-toggle-archived"
            />
        </x-dashy.tooltip>

        <x-dashy.tooltip :text="__('New task')" position="bottom" align="end">
            <x-dashy.button
                wire:click="openCreateTask"
                variant="filled"
                size="sm"
                iconOnly
                icon="plus"
                :aria-label="__('New task')"
                data-test="tasks-header-add"
            />
        </x-dashy.tooltip>
    </div>
</div>
