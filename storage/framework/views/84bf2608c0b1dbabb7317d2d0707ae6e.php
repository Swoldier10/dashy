<?php
    use App\Domains\Chat\Enums\MessageRole;
    use App\Domains\Chat\Services\MarkdownRenderer;
    $markdown = app(MarkdownRenderer::class);
?>

<div class="dashy-chat flex h-full min-h-0 flex-1 flex-col" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'chat-panel'; ?>wire:key="chat-panel">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $this->isCodexConnected): ?>
        
        <div class="m-auto flex max-w-md flex-col items-center gap-6 p-10 text-center">
            <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'sparkles','class' => 'size-10','style' => 'color: var(--accent);']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'sparkles','class' => 'size-10','style' => 'color: var(--accent);']); ?>
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
            <div class="space-y-2">
                <h2 class="font-display text-3xl" style="color: var(--ink);">
                    <?php echo e(__('Connect Codex to start chatting')); ?>

                </h2>
                <p class="text-sm" style="color: var(--ink-muted);">
                    <?php echo e(__('Authorise Codex once and your conversations stream straight from the LLM.')); ?>

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
                <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'link','class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'link','class' => 'size-4']); ?>
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
                <?php echo e(__('Connect Codex')); ?>

            </button>
        </div>
    <?php elseif($this->activeChat === null): ?>
        
        <?php
            $pill = $this->dateTimePill;
            $summary = $this->tomorrowSummary;
        ?>
        <div class="flex flex-1 flex-col overflow-y-auto">
            <div class="m-auto flex w-full max-w-3xl flex-col items-center justify-center gap-5 px-4 py-10 sm:gap-7 sm:px-6">
                
                <div
                    class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-wider"
                    style="background-color: var(--surface); border-color: var(--border); color: var(--ink-muted);"
                    data-test="chat-date-pill"
                >
                    <span class="dashy-pulse" aria-hidden="true"></span>
                    <span><?php echo e($pill['date']); ?></span>
                    <span aria-hidden="true">·</span>
                    <span><?php echo e($pill['time']); ?></span>
                </div>

                
                <h1
                    class="text-center font-display text-3xl font-normal leading-tight sm:text-4xl md:text-5xl"
                    style="color: var(--ink); letter-spacing: -0.01em;"
                    data-test="chat-greeting"
                >
                    <?php echo e($this->greeting); ?>.
                </h1>

                
                <p class="max-w-xl text-center text-sm leading-relaxed sm:text-base" style="color: var(--ink-muted);" data-test="chat-subtitle">
                    <?php echo e(__('You have')); ?>

                    <strong style="color: var(--ink);"><?php echo e(trans_choice('{0} no meetings|{1} :count meeting|[2,*] :count meetings', $summary['meetings'], ['count' => $summary['meetings']])); ?></strong>
                    <?php echo e(__('and')); ?>

                    <strong style="color: var(--ink);"><?php echo e(trans_choice('{0} no tasks|{1} :count task|[2,*] :count tasks', $summary['tasks'], ['count' => $summary['tasks']])); ?></strong>
                    <?php echo e(__('on deck for tomorrow. Want me to help you prep?')); ?>

                </p>

                
                <div class="w-full">
                    <?php echo $__env->make('livewire.chat.partials.composer', ['large' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        
        <div
            class="flex-1 overflow-y-auto"
            <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'thread-'.e($activeChatId).''; ?>wire:key="thread-<?php echo e($activeChatId); ?>"
            x-data
            x-init="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
            x-on:livewire-update.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
        >
            <div class="mx-auto w-full max-w-3xl px-4 py-10 sm:px-6">
                <div class="space-y-8">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->threadMessages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($msg->role === MessageRole::User): ?>
                            <div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'msg-'.e($msg->id).''; ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'msg-'.e($msg->id).''; ?>wire:key="msg-<?php echo e($msg->id); ?>" class="flex justify-end">
                                <div class="flex max-w-[85%] flex-col gap-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($msg->attachments)): ?>
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $msg->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $att): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($att['type'] ?? null) === 'image'): ?>
                                                    <a
                                                        href="<?php echo e($att['url'] ?? '#'); ?>"
                                                        target="_blank"
                                                        rel="noopener"
                                                        class="block max-w-[240px] overflow-hidden rounded-xl border"
                                                        style="border-color: var(--border-mid);"
                                                    >
                                                        <img
                                                            src="<?php echo e($att['url'] ?? ''); ?>"
                                                            alt="<?php echo e($att['name'] ?? ''); ?>"
                                                            class="h-auto w-full"
                                                            loading="lazy"
                                                        />
                                                    </a>
                                                <?php elseif(($att['type'] ?? null) === 'audio'): ?>
                                                    <div class="flex max-w-[320px] flex-col gap-1">
                                                        <?php echo $__env->make('livewire.chat.partials.audio-bubble', [
                                                            'url' => $att['url'] ?? '',
                                                            'duration' => $att['duration_seconds'] ?? null,
                                                            'compact' => false,
                                                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($att['transcript'])): ?>
                                                            <p class="px-2 text-xs italic" style="color: var(--ink-muted);">
                                                                <?php echo e($att['transcript']); ?>

                                                            </p>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </div>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(trim((string) $msg->content) !== ''): ?>
                                        <div
                                            class="rounded-2xl px-4 py-3 text-[15px] leading-relaxed"
                                            style="background-color: var(--surface); color: var(--ink);"
                                        >
                                            <div class="whitespace-pre-wrap break-words"><?php echo e($msg->content); ?></div>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div
                                <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'msg-'.e($msg->id).''; ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'msg-'.e($msg->id).''; ?>wire:key="msg-<?php echo e($msg->id); ?>"
                                class="space-y-3"
                                style="color: var(--ink);"
                            >
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(trim((string) $msg->content) !== ''): ?>
                                    <div class="dashy-prose text-[15px] leading-relaxed">
                                        <?php echo $markdown->render($msg->content); ?>

                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($msg->tool_call !== null): ?>
                                    <?php echo $__env->make('livewire.chat.partials.tool-call-card', [
                                        'message' => $msg,
                                        'card' => $this->toolCardFor($msg),
                                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($streamingAssistant !== '' || $isThinking): ?>
                        <div
                            class="text-[15px] leading-relaxed"
                            style="color: var(--ink);"
                            <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'streaming-'.e($activeChatId).''; ?>wire:key="streaming-<?php echo e($activeChatId); ?>"
                        >
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($streamingAssistant !== ''): ?>
                                <div class="whitespace-pre-wrap break-words" wire:stream="streamingAssistant"><?php echo e($streamingAssistant); ?></div>
                            <?php else: ?>
                                <div class="flex items-center gap-1.5 py-2">
                                    <span
                                        class="size-1.5 animate-pulse rounded-full [animation-delay:0ms]"
                                        style="background-color: var(--ink-muted);"
                                    ></span>
                                    <span
                                        class="size-1.5 animate-pulse rounded-full [animation-delay:150ms]"
                                        style="background-color: var(--ink-muted);"
                                    ></span>
                                    <span
                                        class="size-1.5 animate-pulse rounded-full [animation-delay:300ms]"
                                        style="background-color: var(--ink-muted);"
                                    ></span>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="shrink-0 px-3 pb-4 pt-2 sm:px-4 sm:pb-5">
            <div class="mx-auto w-full max-w-3xl">
                <?php echo $__env->make('livewire.chat.partials.composer', ['large' => false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <p class="mt-2 text-center text-xs" style="color: var(--ink-dim);">
                    <?php echo e(__('Codex can make mistakes. Verify important details.')); ?>

                </p>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <style>
        .dashy-prose > * + * { margin-top: 0.85rem; }
        .dashy-prose h1, .dashy-prose h2, .dashy-prose h3, .dashy-prose h4 {
            font-family: 'Fraunces', ui-serif, Georgia, serif;
            font-weight: 500;
            line-height: 1.25;
            margin-top: 1.5rem;
            color: var(--ink);
            letter-spacing: -0.01em;
        }
        .dashy-prose h1 { font-size: 1.6rem; }
        .dashy-prose h2 { font-size: 1.3rem; }
        .dashy-prose h3 { font-size: 1.1rem; }
        .dashy-prose p { line-height: 1.7; }
        .dashy-prose strong { font-weight: 600; color: var(--ink); }
        .dashy-prose em { font-style: italic; }
        .dashy-prose a { color: var(--blue); text-decoration: underline; text-underline-offset: 2px; }
        .dashy-prose ul, .dashy-prose ol { padding-left: 1.5rem; }
        .dashy-prose ul { list-style: disc; }
        .dashy-prose ol { list-style: decimal; }
        .dashy-prose li { margin-top: 0.25rem; line-height: 1.6; }
        .dashy-prose li > p { margin: 0; }
        .dashy-prose blockquote {
            border-left: 3px solid var(--border-mid);
            padding-left: 1rem;
            color: var(--ink-muted);
            font-style: italic;
        }
        .dashy-prose code {
            font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
            font-size: 0.9em;
            background: var(--surface-2);
            padding: 0.125rem 0.375rem;
            border-radius: 0.25rem;
            color: var(--ink);
        }
        .dashy-prose pre {
            background: var(--surface-2);
            color: var(--ink);
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            overflow-x: auto;
            font-size: 0.9rem;
            line-height: 1.55;
            border: 1px solid var(--border-mid);
        }
        .dashy-prose pre code {
            background: transparent;
            padding: 0;
            border-radius: 0;
            color: inherit;
        }
        .dashy-prose hr {
            border: none;
            border-top: 1px solid var(--border);
            margin: 1.5rem 0;
        }
        .dashy-prose table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .dashy-prose th, .dashy-prose td {
            border: 1px solid var(--border-mid);
            padding: 0.5rem 0.75rem;
            text-align: left;
        }
        .dashy-prose th {
            background: var(--surface-2);
            font-weight: 600;
            color: var(--ink);
        }
        .dashy-composer-box {
            background-color: var(--bg-deep);
            border-color: var(--border-mid);
            box-shadow:
                0 1px 2px rgba(var(--ink-rgb), 0.04),
                0 10px 28px -12px rgba(var(--ink-rgb), 0.12);
        }
        .dashy-composer-box.is-dragging { border-color: var(--accent); }
        .dashy-chat .dashy-composer-editor:focus { outline: none; box-shadow: none; }
        .dashy-chat .dashy-mention {
            display: inline-flex;
            align-items: center;
            padding: 1px 8px;
            border-radius: 9999px;
            background: rgba(89, 146, 198, 0.18);
            color: var(--blue);
            font-weight: 500;
            font-size: 0.92em;
            line-height: 1.4;
            user-select: none;
            white-space: nowrap;
            vertical-align: baseline;
        }
        .dashy-chat *::-webkit-scrollbar { width: 8px; height: 8px; }
        .dashy-chat *::-webkit-scrollbar-track { background: transparent; }
        .dashy-chat *::-webkit-scrollbar-thumb {
            background: var(--border-mid);
            border-radius: 9999px;
        }
        .dashy-chat *::-webkit-scrollbar-thumb:hover { background: var(--border-strong); }
    </style>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/chat/chat-panel.blade.php ENDPATH**/ ?>