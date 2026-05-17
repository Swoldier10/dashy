<div class="m-auto flex max-w-md flex-col items-center gap-4 p-8 text-center">
    <div class="flex size-14 items-center justify-center rounded-2xl"
         style="background-color: color-mix(in srgb, var(--blue) 14%, transparent);">
        <x-dashy.icon name="flag" class="size-7" style="color: var(--blue);" />
    </div>
    <h2 class="font-display text-2xl" style="color: var(--ink);">{{ __('No statuses yet') }}</h2>
    <p class="text-sm leading-relaxed" style="color: var(--ink-muted);">
        {{ __('Add at least one status to this project before you can create tasks. Open project settings from the sidebar.') }}
    </p>
</div>
