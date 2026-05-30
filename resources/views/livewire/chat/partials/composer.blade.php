{{--
    Composer partial — used both in the empty-state (large, centered) and
    pinned at the bottom of an active chat thread. The `large` flag toggles
    sizing/padding without duplicating the inner markup.

    Input is a contenteditable region (not a textarea) so project mentions
    can render as inline pills next to the user's text. The serialized text
    (with chip text inlined as "@Project") is mirrored to $wire.message on
    every input event, and the server dispatches `composer-reset` after a
    successful send to clear the editor.
--}}
@php $large = $large ?? false; @endphp

<div
    x-data="{
        dragDepth: 0,
        get isDragging() { return this.dragDepth > 0; },
        get hasVoice() {
            return $wire.persistedAttachments
                ? $wire.persistedAttachments.some(a => a && a.type === 'audio')
                : false;
        },
        isEmpty: true,
        recording: false,
        recorder: null,
        chunks: [],
        recordSeconds: 0,
        recordTimer: null,
        async startRecording() {
            const wire = this.$wire;
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                this.chunks = [];
                this.recorder = new MediaRecorder(stream);
                const startedAt = performance.now();
                this.recorder.ondataavailable = e => { if (e.data && e.data.size > 0) this.chunks.push(e.data); };
                this.recorder.onstop = () => {
                    try {
                        const mimeType = this.recorder.mimeType || 'audio/webm';
                        const blob = new Blob(this.chunks, { type: mimeType });
                        const ext = (mimeType.includes('mp4') || mimeType.includes('m4a')) ? 'm4a' : 'webm';
                        const centiseconds = Math.max(0, Math.round((performance.now() - startedAt) / 10));
                        const filename = 'voice-' + centiseconds + 'cs.' + ext;
                        const file = new File([blob], filename, { type: mimeType });
                        wire.upload(
                            'voiceUpload',
                            file,
                            () => {},
                            err => console.error('Voice upload failed', err),
                        );
                    } catch (err) {
                        console.error('Voice upload error', err);
                    } finally {
                        stream.getTracks().forEach(t => t.stop());
                    }
                };
                this.recorder.start();
                this.recording = true;
                this.recordSeconds = 0;
                this.recordTimer = setInterval(() => this.recordSeconds++, 1000);
            } catch (err) {
                console.error('Microphone permission denied or unavailable', err);
                this.recording = false;
            }
        },
        stopRecording() {
            try { this.recorder?.stop(); } catch (e) {}
            this.recording = false;
            if (this.recordTimer) { clearInterval(this.recordTimer); this.recordTimer = null; }
        },
        formatDuration(s) {
            const m = Math.floor(s / 60).toString().padStart(2, '0');
            const ss = (s % 60).toString().padStart(2, '0');
            return m + ':' + ss;
        },
        handleDragEnter(e) {
            if (Array.from(e.dataTransfer?.types ?? []).includes('Files')) {
                e.preventDefault();
                this.dragDepth++;
            }
        },
        handleDragLeave() {
            if (this.dragDepth > 0) this.dragDepth--;
        },
        handleDrop(e) {
            e.preventDefault();
            this.dragDepth = 0;
            const files = Array.from(e.dataTransfer?.files ?? []).filter(f => f.type.startsWith('image/'));
            if (files.length === 0) return;
            this.uploadImages(files);
        },
        handleWindowPaste(e) {
            // Only handle pastes that did not originate inside the editor —
            // the editor has its own handler that also covers plain-text paste.
            if (this.$refs.editor && e.target instanceof Node && this.$refs.editor.contains(e.target)) return;
            const items = Array.from(e.clipboardData?.items ?? []);
            const files = items
                .filter(i => i.kind === 'file' && (i.type ?? '').startsWith('image/'))
                .map(i => i.getAsFile())
                .filter(Boolean);
            if (files.length === 0) return;
            e.preventDefault();
            this.uploadImages(files);
        },
        handleEditorPaste(e) {
            const items = Array.from(e.clipboardData?.items ?? []);
            const files = items
                .filter(i => i.kind === 'file' && (i.type ?? '').startsWith('image/'))
                .map(i => i.getAsFile())
                .filter(Boolean);
            if (files.length > 0) {
                e.preventDefault();
                this.uploadImages(files);
                return;
            }
            // Strip formatting — insert plain text only.
            e.preventDefault();
            const text = e.clipboardData?.getData('text/plain') ?? '';
            this.insertTextAtCaret(text);
        },
        uploadImages(files) {
            const wire = this.$wire;
            files.forEach((file, i) => {
                wire.upload(
                    'imageUploads.' + i,
                    file,
                    () => {},
                    err => console.error('Image upload failed', err),
                );
            });
        },
        serialize() {
            const root = this.$refs.editor;
            if (!root) return '';
            let text = '';
            const walk = (node) => {
                if (node.nodeType === Node.TEXT_NODE) {
                    text += node.textContent;
                    return;
                }
                if (node.nodeType !== Node.ELEMENT_NODE) return;
                if (node.dataset && node.dataset.projectId) {
                    text += '@' + node.textContent;
                    return;
                }
                if (node.tagName === 'BR') {
                    text += '\n';
                    return;
                }
                if (['DIV', 'P'].includes(node.tagName) && text !== '' && !text.endsWith('\n')) {
                    text += '\n';
                }
                for (const child of node.childNodes) walk(child);
            };
            for (const child of root.childNodes) walk(child);
            return text;
        },
        syncWire() {
            const text = this.serialize();
            this.isEmpty = text.trim() === '';
            this.$wire.set('message', text, false);
        },
        submitMessage() {
            if (this.recording) return;
            this.syncWire();
            const hasText = this.serialize().trim() !== '';
            const hasAtt = this.$wire.persistedAttachments && this.$wire.persistedAttachments.length > 0;
            if (!hasText && !hasAtt) return;
            this.$wire.sendMessage();
        },
        resetComposer() {
            const root = this.$refs.editor;
            if (root) root.innerHTML = '';
            this.isEmpty = true;
        },
        focusEditor() {
            const root = this.$refs.editor;
            if (!root) return;
            root.focus();
            const range = document.createRange();
            range.selectNodeContents(root);
            range.collapse(false);
            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        },
        insertProject(id, name) {
            const root = this.$refs.editor;
            if (!root) return;
            const chip = document.createElement('span');
            chip.setAttribute('contenteditable', 'false');
            chip.dataset.projectId = String(id);
            chip.className = 'dashy-mention';
            chip.textContent = name;
            const last = root.lastChild;
            const needsLeadingSpace = last && !(last.nodeType === Node.TEXT_NODE && /\s$/.test(last.textContent));
            if (needsLeadingSpace) root.appendChild(document.createTextNode(' '));
            root.appendChild(chip);
            root.appendChild(document.createTextNode(' '));
            this.focusEditor();
            this.syncWire();
        },
        insertTextAtCaret(text) {
            const root = this.$refs.editor;
            if (!root) return;
            const sel = window.getSelection();
            if (!sel || sel.rangeCount === 0 || !root.contains(sel.anchorNode)) {
                root.appendChild(document.createTextNode(text));
            } else {
                const range = sel.getRangeAt(0);
                range.deleteContents();
                const node = document.createTextNode(text);
                range.insertNode(node);
                range.setStartAfter(node);
                range.collapse(true);
                sel.removeAllRanges();
                sel.addRange(range);
            }
            this.syncWire();
        },
    }"
    x-on:paste.window="handleWindowPaste($event)"
    x-on:composer-reset.window="resetComposer()"
    x-on:composer-insert-project="insertProject($event.detail.id, $event.detail.name)"
    class="w-full space-y-3"
>
    {{-- Bordered composer box --}}
    <div
        @class([
            'group/composer dashy-composer-box w-full rounded-2xl border transition',
        ])
        x-bind:class="{ 'is-dragging': isDragging }"
        x-on:dragenter.prevent="handleDragEnter($event)"
        x-on:dragover.prevent
        x-on:dragleave.prevent="handleDragLeave()"
        x-on:drop.prevent="handleDrop($event)"
    >
        <form x-on:submit.prevent="submitMessage()">
            {{-- Thumbnails row (renders only when something is attached) --}}
            @if ($persistedAttachments !== [])
                <div class="flex flex-wrap gap-2 px-3 pt-3 sm:px-4 sm:pt-4" data-test="composer-attachments">
                    @foreach ($persistedAttachments as $i => $att)
                        @if (($att['type'] ?? null) === 'image')
                            <div class="relative size-16 overflow-hidden rounded-lg" wire:key="composer-img-{{ $i }}">
                                <img
                                    src="{{ $att['url'] ?? '' }}"
                                    alt="{{ $att['name'] ?? '' }}"
                                    class="h-full w-full object-cover"
                                />
                                <button
                                    type="button"
                                    wire:click="removeAttachment({{ $i }})"
                                    class="absolute right-0.5 top-0.5 grid size-5 place-items-center rounded-full"
                                    style="background-color: var(--overlay-scrim); color: var(--surface);"
                                    aria-label="{{ __('Remove attachment') }}"
                                    data-test="composer-remove-{{ $i }}"
                                >
                                    <x-dashy.icon name="x-mark" class="size-3" />
                                </button>
                            </div>
                        @elseif (($att['type'] ?? null) === 'audio')
                            <div class="flex items-center gap-2" wire:key="composer-audio-{{ $i }}">
                                @include('livewire.chat.partials.audio-bubble', [
                                    'url' => $att['url'] ?? '',
                                    'duration' => $att['duration_seconds'] ?? null,
                                    'compact' => true,
                                ])
                                <button
                                    type="button"
                                    wire:click="removeAttachment({{ $i }})"
                                    aria-label="{{ __('Remove voice message') }}"
                                    data-test="composer-remove-{{ $i }}"
                                >
                                    <x-dashy.icon name="x-mark" class="size-3.5" style="color: var(--ink-dim);" />
                                </button>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            {{-- Input row --}}
            <div class="relative px-4 pt-3 sm:px-5 sm:pt-4">
                <div
                    x-ref="editor"
                    wire:ignore
                    contenteditable="true"
                    role="textbox"
                    aria-multiline="true"
                    aria-label="{{ __('Message') }}"
                    data-test="chat-composer"
                    class="dashy-composer-editor block w-full whitespace-pre-wrap break-words text-[15px] leading-relaxed outline-none"
                    style="color: var(--ink); min-height: 24px; max-height: {{ $large ? 200 : 160 }}px; overflow-y: auto;"
                    x-on:input="syncWire()"
                    x-on:paste="handleEditorPaste($event)"
                    x-on:keydown.enter="if (! $event.shiftKey) { $event.preventDefault(); submitMessage(); }"
                ></div>
                <div
                    x-show="isEmpty"
                    x-cloak
                    aria-hidden="true"
                    class="pointer-events-none absolute left-4 top-3 text-[15px] leading-relaxed sm:left-5 sm:top-4"
                    style="color: var(--ink-dim);"
                >{{ $large ? __('Ask anything, or describe what you want to get done…') : __('Reply to Codex…') }}</div>
            </div>

            {{-- Toolbar row --}}
            <div class="flex items-center justify-between gap-2 px-3 pb-2 pt-2 sm:px-4 sm:pb-2.5">
                {{-- Left: attach (browse) + mic --}}
                <div class="flex items-center gap-1">
                    {{-- Hidden file input for browse --}}
                    <input
                        type="file"
                        wire:model="imageUploads"
                        id="composer-images-{{ $large ? 'large' : 'small' }}"
                        class="sr-only"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                        multiple
                    />
                    <label
                        for="composer-images-{{ $large ? 'large' : 'small' }}"
                        class="flex size-8 cursor-pointer items-center justify-center rounded-full transition"
                        style="color: var(--ink-muted);"
                        onmouseover="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--ink)';"
                        onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--ink-muted)';"
                        aria-label="{{ __('Add image') }}"
                        data-test="composer-attach"
                    >
                        <x-dashy.icon name="paper-clip" class="size-4" />
                    </label>

                    {{-- Mic --}}
                    <button
                        type="button"
                        x-on:click="recording ? stopRecording() : startRecording()"
                        x-bind:disabled="hasVoice"
                        class="flex h-8 items-center justify-center gap-1.5 rounded-full px-2 transition disabled:cursor-not-allowed disabled:opacity-30"
                        x-bind:style="recording
                            ? 'background-color: var(--state-error); color: var(--ink);'
                            : 'background-color: transparent; color: var(--ink-muted);'"
                        x-bind:aria-label="recording ? '{{ __('Stop recording') }}' : '{{ __('Record voice message') }}'"
                        data-test="composer-mic"
                    >
                        <template x-if="!recording">
                            <x-dashy.icon name="microphone" class="size-4" />
                        </template>
                        <template x-if="recording">
                            <span class="flex items-center gap-1.5">
                                <span class="size-1.5 animate-pulse rounded-full" style="background-color: var(--ink);"></span>
                                <span class="text-xs tabular-nums" x-text="formatDuration(recordSeconds)"></span>
                            </span>
                        </template>
                    </button>
                </div>

                {{-- Right: stop (when the assistant is working) + model badge + send --}}
                <div class="flex items-center gap-2">
                    @if ($isThinking || $streamingAssistant !== '')
                        <button
                            type="button"
                            wire:click="requestStop"
                            wire:loading.attr="disabled"
                            class="flex h-8 items-center justify-center gap-1.5 rounded-full border px-3 text-xs font-medium transition"
                            style="border-color: var(--state-error); color: var(--state-error); background-color: transparent;"
                            aria-label="{{ __('Stop') }}"
                            data-test="chat-stop"
                        >
                            <x-dashy.icon name="stop-circle" class="size-3.5" />
                            <span>{{ __('Stop') }}</span>
                        </button>
                    @endif

                    @if ($large)
                        <span class="flex items-center gap-1 text-xs" style="color: var(--ink-muted);">
                            <x-dashy.icon name="sparkles" class="size-3.5" />
                            <span>{{ __('Auto-selecting best model') }}</span>
                        </span>
                    @else
                        <span class="text-xs" style="color: var(--ink-muted);">
                            {{ $this->modelLabel }}
                        </span>
                    @endif

                    <button
                        type="submit"
                        x-bind:disabled="recording || (isEmpty && (!$wire.persistedAttachments || $wire.persistedAttachments.length === 0))"
                        class="flex size-8 shrink-0 items-center justify-center rounded-full transition disabled:cursor-not-allowed disabled:opacity-30"
                        style="background-color: var(--surface-2); color: var(--ink-muted);"
                        onmouseover="if (!this.disabled) { this.style.backgroundColor='var(--cocoa)'; this.style.color='var(--surface)'; }"
                        onmouseout="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--ink-muted)';"
                        aria-label="{{ __('Send') }}"
                        data-test="chat-send"
                    >
                        <x-dashy.icon name="arrow-right" class="size-4" />
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Project quick-select under composer (hidden on the chat-home empty state) --}}
    @if (! $large && $this->availableProjects->isNotEmpty())
        <div class="flex justify-center">
            <x-dashy.popover align="center" position="bottom" panelClass="min-w-[240px]">
                <x-slot:trigger>
                    <button
                        type="button"
                        class="flex items-center gap-2 rounded-full border px-4 py-2 text-sm transition"
                        style="border-color: var(--border-mid); color: var(--ink-muted); background-color: transparent;"
                        onmouseover="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--ink)'; this.style.borderColor='var(--border-strong)';"
                        onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--ink-muted)'; this.style.borderColor='var(--border-mid)';"
                        data-test="composer-project-trigger"
                    >
                        <x-dashy.icon name="folder" class="size-4" />
                        <span>{{ __('Project') }}</span>
                        <x-dashy.icon name="chevron-down" class="size-3.5" />
                    </button>
                </x-slot:trigger>

                <x-dashy.menu>
                    @foreach ($this->availableProjects as $project)
                        <x-dashy.menu.item
                            type="button"
                            icon="folder"
                            :data-project-id="$project->id"
                            :data-project-name="$project->name"
                            x-on:click="$dispatch('composer-insert-project', { id: Number($event.currentTarget.dataset.projectId), name: $event.currentTarget.dataset.projectName })"
                            data-test="composer-project-option-{{ $project->id }}"
                        >
                            {{ $project->name }}
                        </x-dashy.menu.item>
                    @endforeach
                </x-dashy.menu>
            </x-dashy.popover>
        </div>
    @endif
</div>

@error('message')
    <p class="mt-2 px-2 text-center text-xs" style="color: var(--state-error);">
        {{ $message }}
    </p>
@enderror
@error('imageUploads.*')
    <p class="mt-2 px-2 text-center text-xs" style="color: var(--state-error);">
        {{ $message }}
    </p>
@enderror
@error('voiceUpload')
    <p class="mt-2 px-2 text-center text-xs" style="color: var(--state-error);">
        {{ $message }}
    </p>
@enderror
