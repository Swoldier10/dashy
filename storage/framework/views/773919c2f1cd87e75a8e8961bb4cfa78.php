
<?php $large = $large ?? false; ?>

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
    
    <div
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'group/composer dashy-composer-box w-full rounded-2xl border transition',
        ]); ?>"
        x-bind:class="{ 'is-dragging': isDragging }"
        x-on:dragenter.prevent="handleDragEnter($event)"
        x-on:dragover.prevent
        x-on:dragleave.prevent="handleDragLeave()"
        x-on:drop.prevent="handleDrop($event)"
    >
        <form x-on:submit.prevent="submitMessage()">
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($persistedAttachments !== []): ?>
                <div class="flex flex-wrap gap-2 px-3 pt-3 sm:px-4 sm:pt-4" data-test="composer-attachments">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $persistedAttachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $att): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($att['type'] ?? null) === 'image'): ?>
                            <div class="relative size-16 overflow-hidden rounded-lg" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'composer-img-'.e($i).''; ?>wire:key="composer-img-<?php echo e($i); ?>">
                                <img
                                    src="<?php echo e($att['url'] ?? ''); ?>"
                                    alt="<?php echo e($att['name'] ?? ''); ?>"
                                    class="h-full w-full object-cover"
                                />
                                <button
                                    type="button"
                                    wire:click="removeAttachment(<?php echo e($i); ?>)"
                                    class="absolute right-0.5 top-0.5 grid size-5 place-items-center rounded-full"
                                    style="background-color: rgba(0, 0, 0, 0.55); color: var(--ink);"
                                    aria-label="<?php echo e(__('Remove attachment')); ?>"
                                    data-test="composer-remove-<?php echo e($i); ?>"
                                >
                                    <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'x-mark','class' => 'size-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'size-3']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $attributes = $__attributesOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__attributesOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $component = $__componentOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__componentOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
                                </button>
                            </div>
                        <?php elseif(($att['type'] ?? null) === 'audio'): ?>
                            <div class="flex items-center gap-2" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'composer-audio-'.e($i).''; ?>wire:key="composer-audio-<?php echo e($i); ?>">
                                <?php echo $__env->make('livewire.chat.partials.audio-bubble', [
                                    'url' => $att['url'] ?? '',
                                    'duration' => $att['duration_seconds'] ?? null,
                                    'compact' => true,
                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                <button
                                    type="button"
                                    wire:click="removeAttachment(<?php echo e($i); ?>)"
                                    aria-label="<?php echo e(__('Remove voice message')); ?>"
                                    data-test="composer-remove-<?php echo e($i); ?>"
                                >
                                    <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'x-mark','class' => 'size-3.5','style' => 'color: var(--ink-dim);']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'size-3.5','style' => 'color: var(--ink-dim);']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $attributes = $__attributesOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__attributesOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $component = $__componentOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__componentOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
                                </button>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <div class="relative px-4 pt-3 sm:px-5 sm:pt-4">
                <div
                    x-ref="editor"
                    wire:ignore
                    contenteditable="true"
                    role="textbox"
                    aria-multiline="true"
                    aria-label="<?php echo e(__('Message')); ?>"
                    data-test="chat-composer"
                    class="dashy-composer-editor block w-full whitespace-pre-wrap break-words text-[15px] leading-relaxed outline-none"
                    style="color: var(--ink); min-height: 24px; max-height: <?php echo e($large ? 200 : 160); ?>px; overflow-y: auto;"
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
                ><?php echo e($large ? __('Ask anything, or describe what you want to get done…') : __('Reply to Codex…')); ?></div>
            </div>

            
            <div class="flex items-center justify-between gap-2 px-3 pb-2 pt-2 sm:px-4 sm:pb-2.5">
                
                <div class="flex items-center gap-1">
                    
                    <input
                        type="file"
                        wire:model="imageUploads"
                        id="composer-images-<?php echo e($large ? 'large' : 'small'); ?>"
                        class="sr-only"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                        multiple
                    />
                    <label
                        for="composer-images-<?php echo e($large ? 'large' : 'small'); ?>"
                        class="flex size-8 cursor-pointer items-center justify-center rounded-full transition"
                        style="color: var(--ink-muted);"
                        onmouseover="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--ink)';"
                        onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--ink-muted)';"
                        aria-label="<?php echo e(__('Add image')); ?>"
                        data-test="composer-attach"
                    >
                        <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'paper-clip','class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'paper-clip','class' => 'size-4']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $attributes = $__attributesOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__attributesOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $component = $__componentOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__componentOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
                    </label>

                    
                    <button
                        type="button"
                        x-on:click="recording ? stopRecording() : startRecording()"
                        x-bind:disabled="hasVoice"
                        class="flex h-8 items-center justify-center gap-1.5 rounded-full px-2 transition disabled:cursor-not-allowed disabled:opacity-30"
                        x-bind:style="recording
                            ? 'background-color: var(--state-error); color: var(--ink);'
                            : 'background-color: transparent; color: var(--ink-muted);'"
                        x-bind:aria-label="recording ? '<?php echo e(__('Stop recording')); ?>' : '<?php echo e(__('Record voice message')); ?>'"
                        data-test="composer-mic"
                    >
                        <template x-if="!recording">
                            <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'microphone','class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'microphone','class' => 'size-4']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $attributes = $__attributesOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__attributesOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $component = $__componentOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__componentOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
                        </template>
                        <template x-if="recording">
                            <span class="flex items-center gap-1.5">
                                <span class="size-1.5 animate-pulse rounded-full" style="background-color: var(--ink);"></span>
                                <span class="text-xs tabular-nums" x-text="formatDuration(recordSeconds)"></span>
                            </span>
                        </template>
                    </button>
                </div>

                
                <div class="flex items-center gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isThinking || $streamingAssistant !== ''): ?>
                        <button
                            type="button"
                            wire:click="requestStop"
                            wire:loading.attr="disabled"
                            class="flex h-8 items-center justify-center gap-1.5 rounded-full border px-3 text-xs font-medium transition"
                            style="border-color: var(--state-error); color: var(--state-error); background-color: transparent;"
                            aria-label="<?php echo e(__('Stop')); ?>"
                            data-test="chat-stop"
                        >
                            <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'stop-circle','class' => 'size-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'stop-circle','class' => 'size-3.5']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $attributes = $__attributesOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__attributesOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $component = $__componentOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__componentOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
                            <span><?php echo e(__('Stop')); ?></span>
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($large): ?>
                        <span class="flex items-center gap-1 text-xs" style="color: var(--ink-muted);">
                            <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'sparkles','class' => 'size-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'sparkles','class' => 'size-3.5']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $attributes = $__attributesOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__attributesOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $component = $__componentOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__componentOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
                            <span><?php echo e(__('Auto-selecting best model')); ?></span>
                        </span>
                    <?php else: ?>
                        <span class="text-xs" style="color: var(--ink-muted);">
                            <?php echo e($this->modelLabel); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <button
                        type="submit"
                        x-bind:disabled="recording || (isEmpty && (!$wire.persistedAttachments || $wire.persistedAttachments.length === 0))"
                        class="flex size-8 shrink-0 items-center justify-center rounded-full transition disabled:cursor-not-allowed disabled:opacity-30"
                        style="background-color: var(--surface-2); color: var(--ink-muted);"
                        onmouseover="if (!this.disabled) { this.style.backgroundColor='var(--cocoa)'; this.style.color='#fff'; }"
                        onmouseout="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--ink-muted)';"
                        aria-label="<?php echo e(__('Send')); ?>"
                        data-test="chat-send"
                    >
                        <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'arrow-right','class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-right','class' => 'size-4']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $attributes = $__attributesOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__attributesOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $component = $__componentOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__componentOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
                    </button>
                </div>
            </div>
        </form>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $large && $this->availableProjects->isNotEmpty()): ?>
        <div class="flex justify-center">
            <?php if (isset($component)) { $__componentOriginal51740eb6737cf901f3c9c7bdbefcd742 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal51740eb6737cf901f3c9c7bdbefcd742 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.popover','data' => ['align' => 'center','position' => 'bottom','panelClass' => 'min-w-[240px]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.popover'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'center','position' => 'bottom','panelClass' => 'min-w-[240px]']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                 <?php $__env->slot('trigger', null, []); ?> 
                    <button
                        type="button"
                        class="flex items-center gap-2 rounded-full border px-4 py-2 text-sm transition"
                        style="border-color: var(--border-mid); color: var(--ink-muted); background-color: transparent;"
                        onmouseover="this.style.backgroundColor='var(--surface-2)'; this.style.color='var(--ink)'; this.style.borderColor='var(--border-strong)';"
                        onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--ink-muted)'; this.style.borderColor='var(--border-mid)';"
                        data-test="composer-project-trigger"
                    >
                        <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'folder','class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'folder','class' => 'size-4']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $attributes = $__attributesOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__attributesOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $component = $__componentOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__componentOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
                        <span><?php echo e(__('Project')); ?></span>
                        <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'chevron-down','class' => 'size-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron-down','class' => 'size-3.5']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $attributes = $__attributesOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__attributesOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal95d44a2f66f034299285b9491205706f)): ?>
<?php $component = $__componentOriginal95d44a2f66f034299285b9491205706f; ?>
<?php unset($__componentOriginal95d44a2f66f034299285b9491205706f); ?>
<?php endif; ?>
                    </button>
                 <?php $__env->endSlot(); ?>

                <?php if (isset($component)) { $__componentOriginalc4943370ebe75f1ac49e333cb23bb6d6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc4943370ebe75f1ac49e333cb23bb6d6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.menu','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.menu'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->availableProjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.menu.item','data' => ['type' => 'button','icon' => 'folder','dataProjectId' => $project->id,'dataProjectName' => $project->name,'xOn:click' => '$dispatch(\'composer-insert-project\', { id: Number($event.currentTarget.dataset.projectId), name: $event.currentTarget.dataset.projectName })','dataTest' => 'composer-project-option-'.e($project->id).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.menu.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','icon' => 'folder','data-project-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($project->id),'data-project-name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($project->name),'x-on:click' => '$dispatch(\'composer-insert-project\', { id: Number($event.currentTarget.dataset.projectId), name: $event.currentTarget.dataset.projectName })','data-test' => 'composer-project-option-'.e($project->id).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                            <?php echo e($project->name); ?>

                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9)): ?>
<?php $attributes = $__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9; ?>
<?php unset($__attributesOriginalc66d4dd0a3c028f164d86e7b26a0a8b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9)): ?>
<?php $component = $__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9; ?>
<?php unset($__componentOriginalc66d4dd0a3c028f164d86e7b26a0a8b9); ?>
<?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc4943370ebe75f1ac49e333cb23bb6d6)): ?>
<?php $attributes = $__attributesOriginalc4943370ebe75f1ac49e333cb23bb6d6; ?>
<?php unset($__attributesOriginalc4943370ebe75f1ac49e333cb23bb6d6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc4943370ebe75f1ac49e333cb23bb6d6)): ?>
<?php $component = $__componentOriginalc4943370ebe75f1ac49e333cb23bb6d6; ?>
<?php unset($__componentOriginalc4943370ebe75f1ac49e333cb23bb6d6); ?>
<?php endif; ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal51740eb6737cf901f3c9c7bdbefcd742)): ?>
<?php $attributes = $__attributesOriginal51740eb6737cf901f3c9c7bdbefcd742; ?>
<?php unset($__attributesOriginal51740eb6737cf901f3c9c7bdbefcd742); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal51740eb6737cf901f3c9c7bdbefcd742)): ?>
<?php $component = $__componentOriginal51740eb6737cf901f3c9c7bdbefcd742; ?>
<?php unset($__componentOriginal51740eb6737cf901f3c9c7bdbefcd742); ?>
<?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
    <p class="mt-2 px-2 text-center text-xs" style="color: var(--state-error);">
        <?php echo e($message); ?>

    </p>
<?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['imageUploads.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
    <p class="mt-2 px-2 text-center text-xs" style="color: var(--state-error);">
        <?php echo e($message); ?>

    </p>
<?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['voiceUpload'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
    <p class="mt-2 px-2 text-center text-xs" style="color: var(--state-error);">
        <?php echo e($message); ?>

    </p>
<?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH /var/www/html/resources/views/livewire/chat/partials/composer.blade.php ENDPATH**/ ?>