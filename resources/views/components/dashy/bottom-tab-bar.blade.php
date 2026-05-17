@php
    $isChatHome = request()->routeIs('chat', 'chat.show');
    $isCalendar = request()->routeIs('calendar');
    $isTasks = request()->routeIs('tasks*');

    $tabs = [
        [
            'label' => __('Chat home'),
            'icon' => 'home',
            'href' => route('chat'),
            'active' => $isChatHome,
        ],
        [
            'label' => __('Calendar'),
            'icon' => 'calendar-days',
            'href' => route('calendar'),
            'active' => $isCalendar,
        ],
        [
            'label' => __('Tasks'),
            'icon' => 'clipboard-document-check',
            'href' => route('tasks'),
            'active' => $isTasks,
        ],
        [
            'label' => __('Settings'),
            'icon' => 'cog-6-tooth',
            'onclick' => "\$store.modals.open('settings')",
            'active' => false,
            'isSeparated' => true,
        ],
    ];
@endphp

<div
    class="pointer-events-none fixed inset-x-3 bottom-3 z-40 flex justify-center md:inset-x-0 lg:left-[280px]"
    style="padding-bottom: env(safe-area-inset-bottom, 0px);"
    data-test="bottom-tab-bar"
>
    <nav
        role="navigation"
        aria-label="{{ __('Primary') }}"
        class="pointer-events-auto flex w-full max-w-md items-center justify-around gap-1 rounded-full border p-2 shadow-lg md:w-auto md:max-w-none md:justify-center md:gap-2 md:px-4 md:py-1.5"
        style="background-color: var(--surface); border-color: var(--border); box-shadow: 0 10px 30px -12px rgba(var(--ink-rgb), 0.18);"
    >
        @foreach ($tabs as $tab)
            @php
                $isActive = (bool) $tab['active'];
                $isDisabled = (bool) ($tab['disabled'] ?? false);
                $isSeparated = (bool) ($tab['isSeparated'] ?? false);
                $hasOnClick = ! empty($tab['onclick']);
            @endphp

            @if ($isSeparated)
                <span class="hidden md:block h-5 w-px mx-1" style="background-color: var(--border);" aria-hidden="true"></span>
            @endif

            @php
                $slug = \Illuminate\Support\Str::slug($tab['label']);
                $attrs = 'data-test="bottom-tab-'.$slug.'" data-active="'.($isActive ? 'true' : 'false').'"';
                $sharedClasses = 'flex min-h-[44px] flex-1 items-center justify-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition md:min-h-0 md:flex-none md:px-3.5 md:py-1.5 md:text-sm';
                $sharedStyle = 'background-color: '.($isActive ? 'var(--cocoa)' : 'transparent').'; color: '.($isActive ? '#fff' : 'var(--ink-muted)').';';
            @endphp

            @if ($isDisabled)
                <span
                    aria-disabled="true"
                    class="flex min-h-[44px] flex-1 items-center justify-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium opacity-40 md:min-h-0 md:flex-none md:px-3.5 md:py-1.5 md:text-sm"
                    style="color: var(--ink-muted);"
                    {!! $attrs !!}
                >
                    <x-dashy.icon :name="$tab['icon']" class="size-4" />
                    <span class="hidden md:inline">{{ $tab['label'] }}</span>
                </span>
            @elseif ($hasOnClick)
                <button
                    type="button"
                    x-on:click="{{ $tab['onclick'] }}"
                    class="{{ $sharedClasses }}"
                    style="{{ $sharedStyle }}"
                    @if (! $isActive)
                        onmouseover="this.style.color='var(--ink)';"
                        onmouseout="this.style.color='var(--ink-muted)';"
                    @endif
                    {!! $attrs !!}
                >
                    <x-dashy.icon :name="$tab['icon']" class="size-4" />
                    <span class="hidden md:inline">{{ $tab['label'] }}</span>
                </button>
            @else
                <a
                    href="{{ $tab['href'] }}"
                    wire:navigate
                    aria-current="{{ $isActive ? 'page' : 'false' }}"
                    class="{{ $sharedClasses }}"
                    style="{{ $sharedStyle }}"
                    @if (! $isActive)
                        onmouseover="this.style.color='var(--ink)';"
                        onmouseout="this.style.color='var(--ink-muted)';"
                    @endif
                    {!! $attrs !!}
                >
                    <x-dashy.icon :name="$tab['icon']" class="size-4" />
                    <span class="hidden md:inline">{{ $tab['label'] }}</span>
                </a>
            @endif
        @endforeach
    </nav>
</div>
