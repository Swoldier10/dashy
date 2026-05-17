@php
    // Reverse-enum order so most-advanced category (closed) shows first,
    // matching the tasks page status group order.
    $categories = array_reverse(\App\Domains\Projects\Enums\ProjectStatusCategory::cases());
    $reorderMethod = $mode === 'create' ? 'reorderBufferedStatuses' : 'reorderStatuses';
    $addMethod     = $mode === 'create' ? 'addBufferedStatus'       : 'addStatus';
@endphp

<div class="flex flex-col gap-4" data-test="project-status-manager-{{ $mode }}">
    @foreach ($categories as $category)
        @php $items = $statusesByCategory[$category->value] ?? []; @endphp
        <section data-category="{{ $category->value }}">
            <header class="flex items-center justify-between pb-2">
                <h3 class="text-xs font-medium uppercase tracking-wider" style="color: var(--ink-dim);">
                    {{ $category->label() }}
                </h3>
            </header>

            <div
                class="flex flex-col gap-1"
                x-data
                x-init="
                    if (window.Sortable) {
                        new window.Sortable($el, {
                            animation: 150,
                            handle: '.dashy-status-handle',
                            onEnd: () => {
                                const ids = [...$el.children].map(el => el.dataset.id).filter(Boolean);
                                $wire.{{ $reorderMethod }}('{{ $category->value }}', ids);
                            },
                        });
                    }
                "
                wire:ignore.self
                data-test="status-list-{{ $mode }}-{{ $category->value }}"
            >
                @foreach ($items as $item)
                    @include('livewire.partials.project-status-row', [
                        'item' => $item,
                        'category' => $category,
                        'mode' => $mode,
                        'canManage' => $canManage,
                    ])
                @endforeach
            </div>

            @if ($canManage)
                <div class="mt-1">
                    <input
                        type="text"
                        class="dashy-input w-full text-sm"
                        placeholder="{{ __('Add status') }}"
                        wire:model="pendingStatusName.{{ $category->value }}"
                        wire:keydown.enter.prevent="{{ $addMethod }}('{{ $category->value }}')"
                        maxlength="60"
                        data-test="add-status-input-{{ $mode }}-{{ $category->value }}"
                    />
                </div>
            @endif
        </section>
    @endforeach
</div>
