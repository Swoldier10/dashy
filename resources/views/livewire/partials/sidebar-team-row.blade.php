@php
    $isOpen = in_array($team->id, $expandedTeams, true);
    $activeProjectId = $activeProjectId ?? null;
@endphp

<div wire:key="sidebar-team-{{ $team->id }}" class="px-1">
    <button
        type="button"
        wire:click="toggleTeam({{ $team->id }})"
        class="flex w-full items-center gap-2 rounded-lg px-2 py-2 text-left transition"
        style="background-color: transparent;"
        onmouseover="this.style.backgroundColor='var(--surface-2)'"
        onmouseout="this.style.backgroundColor='transparent'"
        aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
        data-test="sidebar-team-toggle-{{ $team->id }}"
    >
        <x-dashy.icon
            :name="$isOpen ? 'chevron-down' : 'chevron-right'"
            class="size-4 shrink-0"
            style="color: var(--ink-dim);"
        />

        @if ($team->logo)
            <img
                src="{{ $team->logo }}"
                alt=""
                class="size-6 shrink-0 rounded object-cover"
            />
        @else
            <span
                class="flex size-6 shrink-0 items-center justify-center rounded text-xs font-semibold uppercase"
                style="background-color: var(--surface-2); color: var(--ink);"
                aria-hidden="true"
            >{{ $team->initials() }}</span>
        @endif

        <span class="min-w-0 flex-1 truncate text-base" style="color: var(--ink);">
            {{ $team->name }}
        </span>
    </button>

    @if ($isOpen)
        @php $projects = $projectsByTeamId[$team->id] ?? collect(); @endphp
        <div class="mt-1 mb-2 ml-7 flex flex-col gap-1" data-test="sidebar-team-panel-{{ $team->id }}">
            @foreach ($projects as $project)
                @php
                    $isActiveProject = $activeProjectId === (int) $project->id;
                    $idleBg = $isActiveProject ? 'color-mix(in srgb, var(--blue) 18%, transparent)' : 'transparent';
                    $hoverBg = $isActiveProject ? 'color-mix(in srgb, var(--blue) 22%, transparent)' : 'var(--surface-2)';
                @endphp
                <div
                    wire:key="sidebar-project-{{ $project->id }}"
                    class="group relative flex items-center rounded-md transition"
                    style="background-color: {{ $idleBg }};"
                    onmouseover="this.style.backgroundColor='{{ $hoverBg }}'"
                    onmouseout="this.style.backgroundColor='{{ $idleBg }}'"
                >
                    <a
                        href="{{ route('tasks.show', $project) }}"
                        wire:navigate
                        class="flex min-w-0 flex-1 items-center gap-2 px-2 py-1.5"
                        data-test="sidebar-project-link-{{ $project->id }}"
                        @if ($isActiveProject) aria-current="page" @endif
                    >
                        @if ($project->logo)
                            <img
                                src="{{ $project->logo }}"
                                alt=""
                                class="size-5 shrink-0 rounded object-cover"
                            />
                        @else
                            <span
                                class="flex size-5 shrink-0 items-center justify-center rounded text-[10px] font-semibold uppercase"
                                style="background-color: var(--surface-2); color: var(--ink-muted);"
                                aria-hidden="true"
                            >{{ $project->initials() }}</span>
                        @endif

                        <span class="min-w-0 flex-1 truncate text-sm"
                              style="color: {{ $isActiveProject ? 'var(--ink)' : 'var(--ink-muted)' }};">
                            {{ $project->name }}
                        </span>
                    </a>

                    @if ($canDeleteInTeam)
                        <div class="flex shrink-0 items-center gap-0.5 pr-1.5 opacity-0 transition group-hover:opacity-100 group-focus-within:opacity-100">
                            <button
                                type="button"
                                wire:click.stop="openProjectSettings({{ $project->id }})"
                                class="rounded p-0.5 transition"
                                style="color: var(--ink-dim);"
                                onmouseover="this.style.color='var(--ink)';"
                                onmouseout="this.style.color='var(--ink-dim)';"
                                aria-label="{{ __('Project settings') }}"
                                data-test="sidebar-settings-project-{{ $project->id }}"
                            >
                                <x-dashy.icon name="cog-6-tooth" class="size-3.5" />
                            </button>
                            <button
                                type="button"
                                wire:click.stop="confirmDeleteProject({{ $project->id }})"
                                class="rounded p-0.5 transition"
                                style="color: var(--ink-dim);"
                                onmouseover="this.style.color='var(--state-error)';"
                                onmouseout="this.style.color='var(--ink-dim)';"
                                aria-label="{{ __('Delete project') }}"
                                data-test="sidebar-delete-project-{{ $project->id }}"
                            >
                                <x-dashy.icon name="trash" class="size-3.5" />
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach

            <button
                type="button"
                wire:click="openCreateProject({{ $team->id }})"
                class="flex items-center gap-1.5 rounded-md px-2 py-1.5 text-sm font-medium transition"
                style="color: var(--blue); background-color: color-mix(in srgb, var(--blue) 12%, transparent);"
                onmouseover="this.style.backgroundColor='color-mix(in srgb, var(--blue) 22%, transparent)'"
                onmouseout="this.style.backgroundColor='color-mix(in srgb, var(--blue) 12%, transparent)'"
                data-test="sidebar-create-project-{{ $team->id }}"
            >
                <x-dashy.icon name="plus" class="size-4" />
                <span>{{ __('Create Project') }}</span>
            </button>
        </div>
    @endif
</div>
