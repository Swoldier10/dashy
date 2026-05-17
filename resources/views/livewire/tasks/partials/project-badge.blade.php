@if ($project->logo)
    <img
        src="{{ $project->logo }}"
        alt=""
        class="size-7 shrink-0 rounded-lg object-cover"
    />
@else
    <span
        class="flex size-7 shrink-0 items-center justify-center rounded-lg text-xs font-semibold uppercase"
        style="background-color: var(--surface-2); color: var(--ink);"
        aria-hidden="true"
    >{{ $project->initials() }}</span>
@endif
