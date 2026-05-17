<div>
    <x-dashy.modal name="connect-codex" focusable class="max-w-md" wire:close="cancel">
        <div
            class="space-y-5"
            x-data="{
                interval: null,
                start() {
                    if (this.interval) { return; }
                    this.interval = setInterval(() => $wire.poll(), {{ $pollIntervalMs }});
                },
                stop() {
                    if (this.interval) {
                        clearInterval(this.interval);
                        this.interval = null;
                    }
                },
            }"
            x-init="$watch('$wire.isPolling', polling => polling ? start() : stop())"
            x-effect="$wire.isPolling ? start() : stop()"
            x-on:codex-connected.window="stop()"
        >
            <div>
                <x-dashy.heading size="lg">{{ __('Connect Codex') }}</x-dashy.heading>
                <x-dashy.subheading>
                    {{ __('Open the link below, sign in to your ChatGPT account and enter the one-time code.') }}
                </x-dashy.subheading>
            </div>

            @if ($userCode !== null)
                <div class="space-y-3 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <x-dashy.text variant="subtle" class="text-xs uppercase tracking-wide">{{ __('Step 1 — open this URL') }}</x-dashy.text>
                    <a href="{{ $verificationUrl }}" target="_blank" rel="noopener" class="break-all text-sm underline">
                        {{ $verificationUrl }}
                    </a>

                    <x-dashy.separator variant="subtle" />

                    <x-dashy.text variant="subtle" class="text-xs uppercase tracking-wide">{{ __('Step 2 — enter this code') }}</x-dashy.text>
                    <div class="select-all rounded-md bg-zinc-100 p-3 text-center font-mono text-2xl tracking-[0.3em] dark:bg-zinc-800">
                        {{ $userCode }}
                    </div>

                    <x-dashy.text variant="subtle" class="text-xs">
                        {{ __('Waiting for you to approve…') }}
                    </x-dashy.text>
                </div>
            @endif

            <div class="flex justify-end">
                <x-dashy.button type="button" variant="filled" wire:click="cancel" data-test="cancel-connect-codex">
                    {{ __('Cancel') }}
                </x-dashy.button>
            </div>
        </div>
    </x-dashy.modal>
</div>
