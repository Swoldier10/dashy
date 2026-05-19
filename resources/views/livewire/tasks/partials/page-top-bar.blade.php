@php
    use App\Domains\Teams\Support\TeamColor;

    /**
     * @var iterable<\App\Domains\Teams\Models\Team> $teams
     * @var array<int,int> $teamCounts          // team_id => task count
     * @var int $totalCount                     // count for "Everything"
     * @var ?int $activeTeamId                  // null = Everything
     * @var string $everythingHref              // route('tasks')
     */
    $teams = $teams ?? collect();
    $teamCounts = $teamCounts ?? [];
    $totalCount = $totalCount ?? 0;
    $activeTeamId = $activeTeamId ?? null;
    $everythingHref = $everythingHref ?? route('tasks');
@endphp

<div class="sticky top-0 z-20 border-b"
     style="background-color: var(--bg); border-color: var(--border);"
     data-test="tasks-top-bar">
    {{-- Eyebrow row --}}
    <div class="flex items-center gap-3 px-4 py-3 lg:px-6">
        <span class="text-[11px] font-semibold uppercase tracking-wider"
              style="color: var(--ink-dim);">{{ __('Tasks') }}</span>
        <span class="font-display text-base sm:text-lg" style="color: var(--ink);">{{ __('Workspace') }}</span>
    </div>

    {{-- Team chip strip --}}
    <div class="flex items-center gap-1.5 overflow-x-auto px-4 pb-3 lg:px-6" data-test="tasks-team-chips">
        <a
            href="{{ $everythingHref }}"
            wire:navigate
            class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium transition"
            @aria-current(($activeTeamId === null) ? 'page' : false)
            style="
                background-color: {{ $activeTeamId === null ? 'var(--surface)' : 'transparent' }};
                color: {{ $activeTeamId === null ? 'var(--ink)' : 'var(--ink-muted)' }};
                box-shadow: {{ $activeTeamId === null
                    ? '0 0 0 1px var(--border-mid), 0 1px 2px rgba(var(--ink-rgb), 0.04)'
                    : 'none' }};
            "
            @if ($activeTeamId !== null)
                onmouseover="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--ink)';"
                onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--ink-muted)';"
            @endif
            data-test="tasks-team-chip-everything"
        >
            <span class="font-display text-sm leading-none" style="color: {{ $activeTeamId === null ? 'var(--ink)' : 'var(--ink-muted)' }};">Σ</span>
            <span>{{ __('Everything') }}</span>
            <span class="text-[11px]" style="color: var(--ink-dim);">{{ $totalCount }}</span>
        </a>

        @foreach ($teams as $team)
            @php
                $isActive = $activeTeamId === (int) $team->id;
                $count = (int) ($teamCounts[$team->id] ?? 0);
                $teamColorVar = TeamColor::for($team);
            @endphp
            <a
                href="{{ route('tasks', ['team' => $team->id]) }}"
                wire:navigate
                wire:key="team-chip-{{ $team->id }}"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium transition"
                @aria-current($isActive ? 'page' : false)
                style="
                    background-color: {{ $isActive ? 'var(--surface)' : 'transparent' }};
                    color: {{ $isActive ? 'var(--ink)' : 'var(--ink-muted)' }};
                    box-shadow: {{ $isActive ? '0 0 0 1px var(--border-mid), 0 1px 2px rgba(var(--ink-rgb), 0.04)' : 'none' }};
                "
                @if (! $isActive)
                    onmouseover="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--ink)';"
                    onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--ink-muted)';"
                @endif
                data-test="tasks-team-chip-{{ $team->id }}"
            >
                <span class="inline-block size-1.5 rounded-full"
                      style="background-color: var({{ $teamColorVar }});"></span>
                <span class="truncate">{{ $team->name }}</span>
                <span class="text-[11px]" style="color: var(--ink-dim);">{{ $count }}</span>
            </a>
        @endforeach

        <a
            href="{{ route('teams.index') }}"
            wire:navigate
            class="ml-auto inline-flex shrink-0 items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium transition"
            style="color: var(--ink-muted);"
            onmouseover="this.style.color='var(--ink)'; this.style.backgroundColor='var(--surface-2)';"
            onmouseout="this.style.color='var(--ink-muted)'; this.style.backgroundColor='transparent';"
            data-test="tasks-new-team"
        >
            <x-dashy.icon name="plus" class="size-3.5" />
            <span>{{ __('New team') }}</span>
        </a>
    </div>
</div>
