<div>
    <x-dashy.drawer name="notifications-panel" side="right" size="sm" wire:close="closePanel">
        <div class="flex h-full flex-col">
            <header
                class="flex items-center justify-between gap-2 border-b px-4 py-3"
                style="border-color: var(--border);"
            >
                <h2 class="text-base font-semibold" style="color: var(--ink); font-family: var(--font-display);">
                    {{ __('Notifications') }}
                </h2>
                <div class="flex items-center gap-1">
                    @if (count($this->rows) > 0)
                        <button
                            type="button"
                            wire:click="markAllRead"
                            class="dashy-btn dashy-btn--ghost dashy-btn--sm"
                            data-test="notifications-mark-all"
                        >
                            {{ __('Mark all as read') }}
                        </button>
                    @endif
                    <button
                        type="button"
                        x-on:click="$store.modals.close('notifications-panel')"
                        class="inline-flex size-8 items-center justify-center rounded-md transition"
                        style="color: var(--ink-muted);"
                        onmouseover="this.style.color='var(--ink)'; this.style.backgroundColor='var(--surface-2)';"
                        onmouseout="this.style.color='var(--ink-muted)'; this.style.backgroundColor='transparent';"
                        aria-label="{{ __('Close notifications') }}"
                        data-test="notifications-close"
                    >
                        <x-dashy.icon name="x-mark" class="size-4" />
                    </button>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto" data-test="notifications-list">
                @forelse ($this->rows as $row)
                    <button
                        type="button"
                        wire:click="openNotification({{ $row['id'] }})"
                        wire:key="notification-{{ $row['id'] }}"
                        class="flex min-h-[44px] w-full items-start gap-3 border-b px-4 py-3 text-left transition"
                        style="border-color: var(--border); background-color: {{ $row['read'] ? 'transparent' : 'var(--surface-2)' }};"
                        onmouseover="this.style.backgroundColor='var(--bg)';"
                        onmouseout="this.style.backgroundColor='{{ $row['read'] ? 'transparent' : 'var(--surface-2)' }}';"
                        data-test="notification-row-{{ $row['id'] }}"
                        data-read="{{ $row['read'] ? 'true' : 'false' }}"
                    >
                        <span
                            class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full"
                            style="background-color: var(--surface-2); color: var(--ink-muted);"
                            aria-hidden="true"
                        >
                            <x-dashy.icon :name="$row['icon']" class="size-4" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm leading-snug {{ $row['read'] ? '' : 'font-medium' }}" style="color: var(--ink);">
                                {{ $row['title'] }}
                            </span>
                            @if ($row['body'])
                                <span class="mt-0.5 block truncate text-xs" style="color: var(--ink-muted);">
                                    {{ $row['body'] }}
                                </span>
                            @endif
                            <span class="mt-0.5 block text-[11px]" style="color: var(--ink-dim);">
                                {{ $row['time'] }}
                            </span>
                        </span>
                        @unless ($row['read'])
                            <span
                                class="mt-2 size-1.5 shrink-0 rounded-full"
                                style="background-color: var(--blue);"
                                aria-label="{{ __('Unread') }}"
                            ></span>
                        @endunless
                    </button>
                @empty
                    <div class="px-4 py-10">
                        <x-dashy.empty-state
                            icon="bell"
                            :title="__('No notifications')"
                            :description="__('Activity on your tasks, teams, and calendar will show up here.')"
                        />
                    </div>
                @endforelse
            </div>
        </div>
    </x-dashy.drawer>
</div>
