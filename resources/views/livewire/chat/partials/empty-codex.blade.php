<div class="m-auto flex max-w-md flex-col items-center gap-6 p-10 text-center">
    <x-dashy.icon name="sparkles" class="size-10" style="color: var(--accent);" />
    <div class="space-y-2">
        <h2 class="font-display text-3xl" style="color: var(--ink);">
            {{ __('Connect Codex to start chatting') }}
        </h2>
        <p class="text-sm" style="color: var(--ink-muted);">
            {{ __('Authorise Codex once and your conversations stream straight from the LLM.') }}
        </p>
    </div>
    <button
        type="button"
        x-on:click="$dispatch('open-connect-codex')"
        class="flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-medium transition"
        style="background-color: var(--blue); color: white;"
        onmouseover="this.style.opacity='0.9'"
        onmouseout="this.style.opacity='1'"
        data-test="connect-codex-from-chat"
    >
        <x-dashy.icon name="link" class="size-4" />
        {{ __('Connect Codex') }}
    </button>
</div>
