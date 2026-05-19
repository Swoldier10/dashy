@php
    use App\Domains\Teams\Support\TeamColor;

    /**
     * @var iterable<\App\Domains\Teams\Models\Team> $teams
     * @var array<int, \Illuminate\Support\Collection<int, \App\Domains\Projects\Models\Project>> $projectsByTeamId
     * @var array<int,int> $projectTaskCounts   // project_id => task count
     * @var int $totalCount                     // count for "Everything" (all teams)
     * @var ?int $activeProjectId               // null when on /tasks
     * @var bool $isEverythingActive            // highlights the "All …" entry
     * @var ?\App\Domains\Teams\Models\Team $activeTeam   // null when "Everything" tab is active
     * @var int $activeTeamCount                // count for the active team (used when $activeTeam set)
     */
    $teams = $teams ?? collect();
    $projectsByTeamId = $projectsByTeamId ?? [];
    $projectTaskCounts = $projectTaskCounts ?? [];
    $totalCount = $totalCount ?? 0;
    $activeProjectId = $activeProjectId ?? null;
    $isEverythingActive = $isEverythingActive ?? ($activeProjectId === null);
    $activeTeam = $activeTeam ?? null;
    $activeTeamCount = $activeTeamCount ?? 0;
    $teamProjects = $activeTeam ? ($projectsByTeamId[$activeTeam->id] ?? collect()) : collect();
@endphp

<aside class="flex w-full shrink-0 flex-col gap-2 md:w-56 lg:h-full lg:w-60 lg:overflow-y-auto lg:py-4 xl:w-64"
       data-test="workspace-sidebar">
    <div class="rounded-xl lg:flex-1"
         style="background-color: var(--surface-2); box-shadow: 0 1px 2px rgba(var(--ink-rgb), 0.04), 0 1px 0 0 var(--border) inset;">
        {{-- Header --}}
        <div class="flex items-center border-b px-3 py-2.5"
             style="border-color: var(--border);">
            <span class="text-[11px] font-semibold uppercase tracking-wider"
                  style="color: var(--ink-dim);">{{ __('Projects') }}</span>
        </div>

        @if ($activeTeam)
            {{-- Single-team view: "All in team" entry + that team's projects flat --}}
            <div class="p-2">
                <a
                    href="{{ route('tasks', ['team' => $activeTeam->id]) }}"
                    wire:navigate
                    @if ($isEverythingActive) aria-current="page" @endif
                    class="flex items-center gap-2 rounded-md px-2 py-2 text-sm transition"
                    style="
                        background-color: {{ $isEverythingActive ? 'var(--surface)' : 'transparent' }};
                        color: {{ $isEverythingActive ? 'var(--ink)' : 'var(--ink-muted)' }};
                        box-shadow: {{ $isEverythingActive ? '0 1px 2px rgba(var(--ink-rgb), 0.06)' : 'none' }};
                    "
                    @if (! $isEverythingActive)
                        onmouseover="this.style.backgroundColor='var(--bg)'; this.style.color='var(--ink)';"
                        onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--ink-muted)';"
                    @endif
                    data-test="workspace-sidebar-everything"
                >
                    <span class="flex size-6 shrink-0 items-center justify-center rounded-md font-display text-sm"
                          style="background-color: var(--accent); color: var(--cocoa);">Σ</span>
                    <span class="min-w-0 flex-1 truncate">{{ __('All in team') }}</span>
                    <span class="text-xs" style="color: var(--ink-dim);">{{ $activeTeamCount }}</span>
                </a>

                <div class="mt-1 flex flex-col gap-1">
                    @foreach ($teamProjects as $project)
                        @php
                            $isActive = (int) $project->id === (int) $activeProjectId;
                            $count = (int) ($projectTaskCounts[$project->id] ?? 0);
                        @endphp
                        <a
                            href="{{ route('tasks.show', $project) }}"
                            wire:navigate
                            wire:key="ws-project-{{ $project->id }}"
                            @if ($isActive) aria-current="page" @endif
                            class="flex items-center gap-2 rounded-md px-2 py-2 text-sm transition"
                            style="
                                background-color: {{ $isActive ? 'var(--surface)' : 'transparent' }};
                                color: {{ $isActive ? 'var(--ink)' : 'var(--ink-muted)' }};
                                box-shadow: {{ $isActive ? '0 1px 2px rgba(var(--ink-rgb), 0.06)' : 'none' }};
                            "
                            @if (! $isActive)
                                onmouseover="this.style.backgroundColor='var(--bg)'; this.style.color='var(--ink)';"
                                onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--ink-muted)';"
                            @endif
                            data-test="workspace-sidebar-project-{{ $project->id }}"
                        >
                            @include('livewire.tasks.partials.project-shape', ['project' => $project, 'size' => 'xs'])
                            <span class="min-w-0 flex-1 truncate">{{ $project->name }}</span>
                            <span class="text-xs" style="color: var(--ink-dim);">{{ $count }}</span>
                        </a>
                    @endforeach

                    {{-- New-project trigger. Dispatches to the AppSidebar Livewire
                         component which owns the create-project modal. --}}
                    <button
                        type="button"
                        wire:click="$dispatch('open-create-project', { teamId: {{ $activeTeam->id }} })"
                        class="mt-1 flex items-center gap-2 rounded-md px-2 py-2 text-sm transition"
                        style="color: var(--blue); background-color: transparent;"
                        onmouseover="this.style.backgroundColor='color-mix(in srgb, var(--blue) 10%, transparent)'"
                        onmouseout="this.style.backgroundColor='transparent'"
                        data-test="workspace-sidebar-create-project-{{ $activeTeam->id }}"
                    >
                        <span class="flex size-6 shrink-0 items-center justify-center rounded-md"
                              style="background-color: color-mix(in srgb, var(--blue) 14%, transparent);">
                            <x-dashy.icon name="plus" class="size-3.5" />
                        </span>
                        <span class="min-w-0 flex-1 truncate text-left">{{ __('New project') }}</span>
                    </button>
                </div>
            </div>
        @else
            {{-- "Everything" view: "All tasks" entry + projects grouped by team --}}
            <div class="p-2">
                <a
                    href="{{ route('tasks') }}"
                    wire:navigate
                    @if ($isEverythingActive) aria-current="page" @endif
                    class="flex items-center gap-2 rounded-md px-2 py-2 text-sm transition"
                    style="
                        background-color: {{ $isEverythingActive ? 'var(--surface)' : 'transparent' }};
                        color: {{ $isEverythingActive ? 'var(--ink)' : 'var(--ink-muted)' }};
                        box-shadow: {{ $isEverythingActive ? '0 1px 2px rgba(var(--ink-rgb), 0.06)' : 'none' }};
                    "
                    @if (! $isEverythingActive)
                        onmouseover="this.style.backgroundColor='var(--bg)'; this.style.color='var(--ink)';"
                        onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--ink-muted)';"
                    @endif
                    data-test="workspace-sidebar-everything"
                >
                    <span class="flex size-6 shrink-0 items-center justify-center rounded-md font-display text-sm"
                          style="background-color: var(--accent); color: var(--cocoa);">Σ</span>
                    <span class="min-w-0 flex-1 truncate">{{ __('All tasks (Everything)') }}</span>
                    <span class="text-xs" style="color: var(--ink-dim);">{{ $totalCount }}</span>
                </a>
            </div>

            @foreach ($teams as $team)
                @php
                    $groupProjects = $projectsByTeamId[$team->id] ?? collect();
                    $teamColorVar = TeamColor::for($team);
                @endphp
                <div class="border-t px-2 py-2" style="border-color: var(--border);" wire:key="ws-team-{{ $team->id }}">
                    <div class="flex items-center gap-2 px-2 pb-1">
                        <span class="inline-block size-1.5 rounded-full" style="background-color: var({{ $teamColorVar }});"></span>
                        <span class="text-[11px] font-semibold uppercase tracking-wider"
                              style="color: var(--ink-dim);">{{ $team->name }}</span>
                    </div>

                    <div class="flex flex-col gap-1">
                        @foreach ($groupProjects as $project)
                            @php
                                $isActive = (int) $project->id === (int) $activeProjectId;
                                $count = (int) ($projectTaskCounts[$project->id] ?? 0);
                            @endphp
                            <a
                                href="{{ route('tasks.show', $project) }}?from=everything"
                                wire:navigate
                                wire:key="ws-project-{{ $project->id }}"
                                @if ($isActive) aria-current="page" @endif
                                class="flex items-center gap-2 rounded-md px-2 py-2 text-sm transition"
                                style="
                                    background-color: {{ $isActive ? 'var(--surface)' : 'transparent' }};
                                    color: {{ $isActive ? 'var(--ink)' : 'var(--ink-muted)' }};
                                    box-shadow: {{ $isActive ? '0 1px 2px rgba(var(--ink-rgb), 0.06)' : 'none' }};
                                "
                                @if (! $isActive)
                                    onmouseover="this.style.backgroundColor='var(--bg)'; this.style.color='var(--ink)';"
                                    onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--ink-muted)';"
                                @endif
                                data-test="workspace-sidebar-project-{{ $project->id }}"
                            >
                                @include('livewire.tasks.partials.project-shape', ['project' => $project, 'size' => 'xs'])
                                <span class="min-w-0 flex-1 truncate">{{ $project->name }}</span>
                                <span class="text-xs" style="color: var(--ink-dim);">{{ $count }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</aside>
