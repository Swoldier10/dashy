@php
    $count = $this->unreadCount;
    $badge = $count > 9 ? '9+' : (string) $count;
@endphp

<div wire:poll.visible.30s class="{{ $variant === 'sidebar' ? 'w-full' : 'shrink-0' }}">
    @if ($variant === 'sidebar')
        <button
            type="button"
            wire:click="open"
            class="flex w-full items-center rounded-lg py-2.5 text-sm font-medium transition"
            :class="collapsed ? 'justify-center px-0' : 'gap-3 px-2'"
            style="color: var(--ink-muted);"
            onmouseover="this.style.backgroundColor='var(--bg)'; this.style.color='var(--ink)';"
            onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--ink-muted)';"
            :title="collapsed ? '{{ __('Notifications') }}' : ''"
            data-test="notifications-bell"
        >
            <span class="relative flex size-8 shrink-0 items-center justify-center" style="color: currentColor;">
                <x-dashy.icon name="bell" class="size-6" />
                @if ($count > 0)
                    {{-- Numeric bubble pinned onto the icon so it survives the
                         64px collapsed rail (a 6px dot can't carry a count). --}}
                    <span
                        x-show="collapsed"
                        x-cloak
                        class="dashy-notif-bubble absolute -right-1.5 -top-1 ring-2"
                        style="--tw-ring-color: var(--surface-2);"
                        data-test="notifications-badge"
                    >{{ $badge }}</span>
                @endif
            </span>
            <span x-show="!collapsed" class="flex-1 text-left">{{ __('Notifications') }}</span>
            @if ($count > 0)
                <span x-show="!collapsed" class="dashy-notif-bubble" data-test="notifications-badge">{{ $badge }}</span>
            @endif
        </button>
    @else
        <button
            type="button"
            wire:click="open"
            class="relative flex size-9 shrink-0 items-center justify-center rounded-full transition"
            style="color: var(--ink-muted);"
            onmouseover="this.style.color='var(--ink)';"
            onmouseout="this.style.color='var(--ink-muted)';"
            aria-label="{{ __('Open notifications') }}"
            data-test="notifications-bell"
        >
            <x-dashy.icon name="bell" class="size-6" />
            @if ($count > 0)
                <span
                    class="dashy-notif-bubble absolute -right-0.5 -top-0.5 ring-2"
                    style="--tw-ring-color: var(--surface-2);"
                    data-test="notifications-badge"
                >{{ $badge }}</span>
            @endif
        </button>
    @endif
</div>
