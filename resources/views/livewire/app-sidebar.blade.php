@php
    $isChatRoute = $this->isChatRoute;
    $activeChatId = $this->activeChatId;
    $navItems = [
        ['route' => 'chat', 'label' => __('Chat'), 'icon' => 'chat-bubble-oval-left', 'active' => $this->activeSegment === 'chat'],
        ['route' => 'calendar', 'label' => __('Calendar'), 'icon' => 'calendar-days', 'active' => $this->activeSegment === 'calendar', 'dot' => true],
        ['route' => 'tasks', 'label' => __('Tasks'), 'icon' => 'clipboard-document-check', 'active' => $this->activeSegment === 'tasks'],
    ];
    $user = auth()->user();
@endphp

<div
    x-data="{ collapsed: localStorage.getItem('dashy-sidebar-collapsed') === '1' }"
    x-init="$watch('collapsed', v => localStorage.setItem('dashy-sidebar-collapsed', v ? '1' : '0'))"
    :class="collapsed ? 'lg:w-16' : 'lg:w-[280px]'"
    class="lg:shrink-0 lg:transition-[width] lg:duration-200"
>
    {{-- ────────────────────────────────────────────────────────────────
         Mobile / tablet shell (visible below lg)
         Compact header: logo + search + user avatar; collapsible panel
         for today's agenda and recent chats so the floating bottom bar
         owns primary navigation.
         ──────────────────────────────────────────────────────────────── --}}
    <header
        x-data="{ open: false }"
        class="sticky top-0 z-30 border-b lg:hidden"
        style="background-color: var(--surface-2); border-color: var(--border); color: var(--ink);"
        data-test="mobile-topbar"
    >
        <div class="flex items-center gap-3 px-3 py-3">
            <a href="{{ route('chat') }}" wire:navigate class="flex shrink-0 items-center gap-2" aria-label="Dashy home">
                <span
                    class="flex size-7 items-center justify-center rounded-md font-display text-sm font-semibold"
                    style="background-color: var(--cocoa); color: #fff;"
                    aria-hidden="true"
                >d</span>
                <span class="hidden sm:inline font-display text-base" style="color: var(--ink);">Dashy</span>
            </a>

            <div class="flex-1"></div>

            <button
                type="button"
                x-on:click="$store.modals.open('settings')"
                class="flex size-9 shrink-0 items-center justify-center rounded-full transition"
                style="background-color: var(--cocoa); color: #fff;"
                aria-label="{{ __('Open settings') }}"
                data-test="mobile-user-menu"
            >
                <span class="text-xs font-semibold">{{ $user->initials() }}</span>
            </button>
        </div>

        {{-- Collapsible TODAY agenda --}}
        <div class="px-3 pb-2">
            <button
                type="button"
                x-on:click="open = !open"
                class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-medium transition"
                style="background-color: var(--surface-2); color: var(--ink-muted);"
                :aria-expanded="open ? 'true' : 'false'"
                data-test="mobile-today-toggle"
            >
                <span class="flex items-center gap-2">
                    <x-dashy.icon name="calendar-days" class="size-4" />
                    <span>{{ $this->todayDateLabel }}</span>
                </span>
                <x-dashy.icon name="chevron-down" class="size-4 transition" x-bind:class="open ? 'rotate-180' : ''" />
            </button>

            <div x-show="open" x-cloak x-transition class="mt-2 space-y-1" data-test="mobile-today">
                @forelse ($this->todayAgenda as $row)
                    @include('livewire.partials.sidebar-agenda-row', ['row' => $row])
                @empty
                    <p class="px-1 py-2 text-xs" style="color: var(--ink-dim);">{{ __('Nothing scheduled today.') }}</p>
                @endforelse
            </div>
        </div>

        {{-- Recent chats strip (chat routes only) --}}
        @if ($isChatRoute)
            <div class="flex gap-2 overflow-x-auto px-3 pb-3" data-test="mobile-recents">
                <button
                    type="button"
                    wire:click="startNewChat"
                    class="flex shrink-0 items-center gap-1.5 rounded-full px-3 py-2 text-sm font-medium transition"
                    style="background-color: var(--cocoa); color: #fff;"
                    data-test="mobile-new-chat"
                >
                    <x-dashy.icon name="plus" class="size-4" />
                    <span>{{ __('New') }}</span>
                </button>

                @forelse ($this->chats as $chat)
                    @php $isActive = $activeChatId === $chat->id; @endphp
                    <div
                        wire:key="mobile-chat-{{ $chat->id }}"
                        class="flex shrink-0 items-center rounded-full pr-1.5"
                        style="background-color: {{ $isActive ? 'var(--surface-2)' : 'var(--surface)' }}; border: 1px solid var(--border-mid);"
                    >
                        <a
                            href="{{ route('chat.show', $chat) }}"
                            wire:navigate
                            class="block max-w-[10rem] truncate px-3 py-2 text-sm"
                            style="color: {{ $isActive ? 'var(--ink)' : 'var(--ink-muted)' }};"
                        >
                            {{ $chat->title ?? __('New chat') }}
                        </a>
                        <button
                            type="button"
                            wire:click.stop="confirmDeleteChat({{ $chat->id }})"
                            class="rounded-full p-1 transition"
                            style="color: var(--ink-dim);"
                            onmouseover="this.style.color='var(--ink)';"
                            onmouseout="this.style.color='var(--ink-dim)';"
                            aria-label="{{ __('Delete chat') }}"
                            data-test="mobile-delete-chat-{{ $chat->id }}"
                        >
                            <x-dashy.icon name="x-mark" class="size-3.5" />
                        </button>
                    </div>
                @empty
                    <p class="px-3 py-2 text-sm" style="color: var(--ink-dim);">
                        {{ __('No chats yet.') }}
                    </p>
                @endforelse
            </div>
        @endif

        {{-- Teams used to live here on tasks routes. The Tasks page now
             surfaces them in its own Workspace sidebar (see
             resources/views/livewire/tasks/partials/workspace-sidebar.blade.php). --}}
    </header>

    {{-- ────────────────────────────────────────────────────────────────
         Desktop sidebar (visible at lg+) — matches the mockup
         ──────────────────────────────────────────────────────────────── --}}
    <aside
        class="hidden h-screen w-full shrink-0 flex-col gap-4 overflow-hidden border-r lg:sticky lg:top-0 lg:flex"
        :class="collapsed ? 'p-2 items-center' : 'p-4'"
        style="background-color: var(--surface-2); border-color: var(--border); color: var(--ink);"
        data-test="desktop-sidebar"
    >
        {{-- Logo + collapse/expand toggle. Stacks vertically in the rail
             so both stay on-screen at 64px wide. --}}
        <div
            class="flex w-full gap-2"
            :class="collapsed ? 'flex-col items-center' : 'items-center justify-between px-1'"
        >
            <a
                href="{{ route('chat') }}"
                wire:navigate
                class="flex items-center gap-2"
                aria-label="Dashy home"
            >
                <span
                    class="flex size-7 items-center justify-center rounded-md font-display text-sm font-semibold"
                    style="background-color: var(--cocoa); color: #fff;"
                    aria-hidden="true"
                >d</span>
                <span x-show="!collapsed" class="font-display text-base" style="color: var(--ink);">Dashy</span>
            </a>
            <button
                type="button"
                x-on:click="collapsed = ! collapsed"
                class="inline-flex size-7 shrink-0 items-center justify-center rounded-md transition"
                style="color: var(--ink-muted);"
                onmouseover="this.style.color='var(--ink)'; this.style.backgroundColor='var(--bg)';"
                onmouseout="this.style.color='var(--ink-muted)'; this.style.backgroundColor='transparent';"
                :aria-label="collapsed ? '{{ __('Expand sidebar') }}' : '{{ __('Collapse sidebar') }}'"
                data-test="sidebar-toggle"
            >
                <x-dashy.icon
                    name="chevron-double-left"
                    x-show="!collapsed"
                    class="size-4"
                />
                <x-dashy.icon
                    name="chevron-double-right"
                    x-show="collapsed"
                    x-cloak
                    class="size-4"
                />
            </button>
        </div>

        {{-- Primary nav — labels collapse to icon-only when the rail is narrow. --}}
        <nav
            class="flex w-full flex-col gap-0.5"
            role="navigation"
            aria-label="{{ __('Primary') }}"
        >
            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    wire:navigate
                    aria-current="{{ $item['active'] ? 'page' : 'false' }}"
                    class="flex items-center rounded-lg py-2.5 text-sm font-medium transition"
                    :class="collapsed ? 'justify-center px-0' : 'gap-3 px-2'"
                    style="
                        color: {{ $item['active'] ? 'var(--ink)' : 'var(--ink-muted)' }};
                        background-color: {{ $item['active'] ? 'var(--surface)' : 'transparent' }};
                        box-shadow: {{ $item['active']
                            ? '0 0 0 1px var(--border-mid), 0 1px 2px rgba(var(--ink-rgb), 0.04), 0 6px 14px -6px rgba(var(--ink-rgb), 0.10)'
                            : 'none' }};
                    "
                    @if (! $item['active'])
                        onmouseover="this.style.backgroundColor='var(--bg)'; this.style.color='var(--ink)';"
                        onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--ink-muted)';"
                    @endif
                    :title="collapsed ? '{{ $item['label'] }}' : ''"
                    data-test="sidebar-nav-{{ $item['route'] }}"
                >
                    <span
                        class="relative flex size-8 shrink-0 items-center justify-center"
                        style="color: currentColor;"
                    >
                        <x-dashy.icon :name="$item['icon']" class="size-6" />
                        @if (! empty($item['dot']))
                            {{-- Dot moves onto the icon when collapsed so it
                                 stays visible in the rail. --}}
                            <span
                                x-show="collapsed"
                                x-cloak
                                class="absolute -right-0.5 -top-0.5 size-1.5 rounded-full ring-2"
                                style="background-color: var(--state-error); --tw-ring-color: var(--surface-2);"
                                aria-label="{{ __('Has updates') }}"
                            ></span>
                        @endif
                    </span>
                    <span x-show="!collapsed" class="flex-1">{{ $item['label'] }}</span>
                    @if (! empty($item['dot']))
                        <span
                            x-show="!collapsed"
                            class="size-1.5 rounded-full"
                            style="background-color: var(--state-error);"
                            aria-label="{{ __('Has updates') }}"
                        ></span>
                    @endif
                </a>
            @endforeach
        </nav>

        {{-- + New chat — collapses to a square icon button in the rail. --}}
        @if ($isChatRoute)
            <button
                type="button"
                wire:click="startNewChat"
                class="flex items-center rounded-lg py-2 text-sm font-medium transition"
                :class="collapsed ? 'size-10 self-center justify-center p-0' : 'gap-3 px-3'"
                style="background-color: var(--cocoa); color: #fff;"
                onmouseover="this.style.opacity='0.92'"
                onmouseout="this.style.opacity='1'"
                :title="collapsed ? '{{ __('New chat') }}' : ''"
                data-test="sidebar-new-chat"
            >
                <x-dashy.icon name="plus" class="size-4" />
                <span x-show="!collapsed" class="flex-1 text-left">{{ __('New chat') }}</span>
                <kbd
                    x-show="!collapsed"
                    class="rounded px-1.5 py-0.5 text-[10px] font-medium"
                    style="background-color: rgba(255, 255, 255, 0.16); color: rgba(255, 255, 255, 0.72);"
                    aria-hidden="true"
                >⌘N</kbd>
            </button>
        @endif

        {{-- Today's agenda --}}
        <section x-show="!collapsed" class="flex flex-col gap-2" data-test="sidebar-today">
            <div class="flex items-center justify-between px-1">
                <p class="text-[11px] font-semibold uppercase tracking-wider" style="color: var(--ink-dim);">
                    {{ $this->todayDateLabel }}
                </p>
                <a href="{{ route('calendar') }}" wire:navigate class="text-xs font-medium transition" style="color: var(--ink-muted);" onmouseover="this.style.color='var(--ink)'" onmouseout="this.style.color='var(--ink-muted)'">{{ __('View all') }}</a>
            </div>

            @forelse ($this->todayAgenda as $row)
                @include('livewire.partials.sidebar-agenda-row', ['row' => $row])
            @empty
                <p class="px-1 text-xs" style="color: var(--ink-dim);">{{ __('Nothing scheduled today.') }}</p>
            @endforelse
        </section>

        {{-- Spacer pushes the bottom block (recent chats / user pill) down. --}}
        <div class="flex-1"></div>

        {{-- Recent chats — visible on every route so the user can jump back
             into a conversation from anywhere (matches the design mockup).
             Hidden in the collapsed rail to keep it narrow. --}}
        @if ($this->chats->isNotEmpty())
            <section x-show="!collapsed" class="flex min-h-0 flex-col gap-1" data-test="sidebar-recents">
                <p class="px-1 text-[11px] font-semibold uppercase tracking-wider" style="color: var(--ink-dim);">
                    {{ __('Recent chats') }}
                </p>
                <div class="max-h-[180px] overflow-y-auto pr-1">
                    @forelse ($this->chats as $chat)
                        @php $isActive = $activeChatId === $chat->id; @endphp
                        <div
                            wire:key="sidebar-chat-{{ $chat->id }}"
                            class="group relative flex items-center rounded-lg transition"
                            style="background-color: {{ $isActive ? 'var(--surface-2)' : 'transparent' }};"
                            @if (! $isActive)
                                onmouseover="this.style.backgroundColor='var(--surface-2)';"
                                onmouseout="this.style.backgroundColor='transparent';"
                            @endif
                        >
                            <a
                                href="{{ route('chat.show', $chat) }}"
                                wire:navigate
                                class="flex flex-1 items-center justify-between gap-2 truncate px-3 py-2 text-sm"
                                style="color: {{ $isActive ? 'var(--ink)' : 'var(--ink-muted)' }};"
                            >
                                <span class="truncate">{{ $chat->title ?? __('New chat') }}</span>
                                <span class="shrink-0 text-[11px]" style="color: var(--ink-dim);">{{ $chat->updated_at?->diffForHumans(null, true, true) }}</span>
                            </a>
                            <button
                                type="button"
                                wire:click.stop="confirmDeleteChat({{ $chat->id }})"
                                class="absolute right-1.5 top-1/2 -translate-y-1/2 rounded p-1 opacity-0 transition group-hover:opacity-100 focus:opacity-100"
                                style="color: var(--ink-dim); background-color: var(--surface);"
                                onmouseover="this.style.color='var(--ink)';"
                                onmouseout="this.style.color='var(--ink-dim)';"
                                aria-label="{{ __('Delete chat') }}"
                                data-test="sidebar-delete-chat-{{ $chat->id }}"
                            >
                                <x-dashy.icon name="trash" class="size-3.5" />
                            </button>
                        </div>
                    @empty
                        <p class="px-3 py-2 text-sm" style="color: var(--ink-dim);">
                            {{ __('No chats yet.') }}
                        </p>
                    @endforelse
                </div>
            </section>
        @endif

        {{-- User profile card — opens the global settings modal. Collapses to
             the avatar-only avatar in the rail. --}}
        <div class="mt-2 w-full border-t pt-3" style="border-color: var(--border);">
            <button
                type="button"
                x-on:click="$store.modals.open('settings')"
                class="flex items-center rounded-lg py-2 text-left transition"
                :class="collapsed ? 'w-auto justify-center self-center px-1' : 'w-full gap-3 px-2'"
                style="background-color: transparent;"
                onmouseover="this.style.backgroundColor='var(--surface-2)'"
                onmouseout="this.style.backgroundColor='transparent'"
                :title="collapsed ? '{{ __('Settings') }}' : ''"
                data-test="sidebar-user-menu"
            >
                <span
                    class="flex size-8 shrink-0 items-center justify-center rounded-md text-xs font-semibold"
                    style="background-color: var(--cocoa); color: #fff;"
                >{{ $user->initials() }}</span>
                <div x-show="!collapsed" class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium" style="color: var(--ink);">{{ $user->name }}</p>
                    <p class="truncate text-xs" style="color: var(--ink-muted);">
                        @if ($this->isCodexConnected)
                            {{ __('Pro · :model', ['model' => $this->modelLabel]) }}
                        @else
                            {{ __('Not connected') }}
                        @endif
                    </p>
                </div>
                <x-dashy.icon x-show="!collapsed" name="cog-6-tooth" class="size-4 shrink-0" style="color: var(--ink-muted);" />
            </button>
        </div>
    </aside>

    {{-- Create-project modal --}}
    <x-dashy.modal name="create-project" class="max-w-4xl" wire:close="cancelCreateProject" data-test="create-project-modal">
        <form wire:submit="createProject" class="space-y-4">
            <x-dashy.heading size="lg">{{ __('Create project') }}</x-dashy.heading>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <x-dashy.input
                        wire:model="newProjectName"
                        :label="__('Name')"
                        required
                        maxlength="80"
                        data-test="create-project-name"
                    />

                    <x-dashy.textarea
                        wire:model="newProjectDescription"
                        :label="__('Description')"
                        rows="3"
                        data-test="create-project-description"
                    />

                    <div class="space-y-2">
                        <x-dashy.label>{{ __('Logo (optional)') }}</x-dashy.label>
                        <input
                            type="file"
                            wire:model="newProjectLogo"
                            accept="image/*"
                            class="dashy-input"
                            data-test="create-project-logo"
                        />
                        @if ($newProjectLogo)
                            <img
                                src="{{ $newProjectLogo->temporaryUrl() }}"
                                alt=""
                                class="size-16 rounded object-cover"
                            />
                        @endif
                        @error('newProjectLogo') <p class="dashy-error">{{ $message }}</p> @enderror
                        @error('logo') <p class="dashy-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                @include('livewire.partials.project-status-manager', [
                    'mode' => 'create',
                    'statusesByCategory' => $this->bufferedStatusesByCategory(),
                    'canManage' => true,
                ])
            </div>

            <div class="flex justify-end gap-2">
                <x-dashy.modal.close>
                    <x-dashy.button type="button" variant="filled">{{ __('Cancel') }}</x-dashy.button>
                </x-dashy.modal.close>
                <x-dashy.button type="submit" variant="primary" data-test="confirm-create-project">
                    {{ __('Create') }}
                </x-dashy.button>
            </div>
        </form>
    </x-dashy.modal>

    {{-- Project-settings modal --}}
    <x-dashy.modal name="project-settings" class="max-w-4xl" wire:close="cancelProjectSettings" data-test="project-settings-modal">
        <form wire:submit="updateProject" class="space-y-4">
            <x-dashy.heading size="lg">{{ __('Project settings') }}</x-dashy.heading>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <x-dashy.input
                        wire:model="editProjectName"
                        :label="__('Name')"
                        required
                        maxlength="80"
                        data-test="settings-project-name"
                    />

                    <x-dashy.textarea
                        wire:model="editProjectDescription"
                        :label="__('Description')"
                        rows="3"
                        data-test="settings-project-description"
                    />

                    <div class="space-y-2">
                        <x-dashy.label>{{ __('Logo') }}</x-dashy.label>

                        @if ($editProjectLogo)
                            <img
                                src="{{ $editProjectLogo->temporaryUrl() }}"
                                alt=""
                                class="size-16 rounded object-cover"
                            />
                        @elseif ($editProjectCurrentLogo)
                            <img
                                src="{{ $editProjectCurrentLogo }}"
                                alt=""
                                class="size-16 rounded object-cover"
                            />
                        @endif

                        <input
                            type="file"
                            wire:model="editProjectLogo"
                            accept="image/*"
                            class="dashy-input"
                            data-test="settings-project-logo"
                        />
                        @error('editProjectLogo') <p class="dashy-error">{{ $message }}</p> @enderror
                        @error('logo') <p class="dashy-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                @include('livewire.partials.project-status-manager', [
                    'mode' => 'edit',
                    'statusesByCategory' => $this->editProjectStatusesByCategory,
                    'canManage' => true,
                ])
            </div>

            <div class="flex justify-end gap-2">
                <x-dashy.modal.close>
                    <x-dashy.button type="button" variant="filled">{{ __('Cancel') }}</x-dashy.button>
                </x-dashy.modal.close>
                <x-dashy.button type="submit" variant="primary" data-test="confirm-update-project">
                    {{ __('Save') }}
                </x-dashy.button>
            </div>
        </form>
    </x-dashy.modal>

    {{-- Confirm-project-deletion modal --}}
    <x-dashy.modal
        name="confirm-project-deletion"
        focusable
        class="max-w-md"
        wire:close="cancelDeleteProject"
    >
        <div class="space-y-4">
            <x-dashy.heading size="lg">{{ __('Delete this project?') }}</x-dashy.heading>
            <x-dashy.subheading>
                {{ __('This permanently removes the project. It cannot be undone.') }}
            </x-dashy.subheading>
            <div class="flex justify-end gap-2">
                <x-dashy.modal.close>
                    <x-dashy.button type="button" variant="filled" wire:click="cancelDeleteProject">
                        {{ __('Cancel') }}
                    </x-dashy.button>
                </x-dashy.modal.close>
                <x-dashy.button variant="danger" wire:click="deleteProject" data-test="confirm-delete-project">
                    {{ __('Delete') }}
                </x-dashy.button>
            </div>
        </div>
    </x-dashy.modal>

    {{-- Delete-chat confirmation modal — shared by both shells --}}
    <x-dashy.modal
        name="confirm-chat-deletion"
        focusable
        class="max-w-md"
        wire:close="cancelDeleteChat"
    >
        <div class="space-y-4">
            <x-dashy.heading size="lg">{{ __('Delete this chat?') }}</x-dashy.heading>
            <x-dashy.subheading>
                {{ __('This permanently removes the chat and every message in it. It cannot be undone.') }}
            </x-dashy.subheading>
            <div class="flex justify-end gap-2">
                <x-dashy.modal.close>
                    <x-dashy.button type="button" variant="filled" wire:click="cancelDeleteChat">
                        {{ __('Cancel') }}
                    </x-dashy.button>
                </x-dashy.modal.close>
                <x-dashy.button variant="danger" wire:click="deleteChat" data-test="confirm-delete-chat">
                    {{ __('Delete') }}
                </x-dashy.button>
            </div>
        </div>
    </x-dashy.modal>
</div>
