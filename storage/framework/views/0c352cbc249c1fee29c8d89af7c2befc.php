<?php
    /**
     * @var \App\Domains\Chat\Models\Message $message
     * @var array<string, mixed> $card
     */
    $status = $card['status'] ?? 'pending';
    $isPending = $status === 'pending';
    $editKey = 'toolCallEdits.'.$message->id;
    $validationErrors = (array) ($card['validation_errors'] ?? []);
    $args = is_array($card['arguments'] ?? null) ? $card['arguments'] : [];
    $colors = (array) ($card['available_colors'] ?? []);
    $recurrences = (array) ($card['available_recurrence_freqs'] ?? []);
    $selectedColor = (string) ($args['color'] ?? 'danube');
    $selectedRecurrence = (string) ($args['recurrence_freq'] ?? 'none');
?>

<div
    class="mt-3 overflow-hidden rounded-2xl border"
    style="border-color: var(--border-mid); background-color: var(--surface-2);"
    data-test="tool-call-card"
    data-tool="create_event"
    data-status="<?php echo e($status); ?>"
>
    
    <div
        class="flex items-center gap-2 border-b px-4 py-2.5 text-xs uppercase tracking-wide"
        style="border-color: var(--border); color: var(--ink-muted);"
    >
        <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'calendar-days','class' => 'size-4','style' => 'color: var(--blue);']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'calendar-days','class' => 'size-4','style' => 'color: var(--blue);']); ?>
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
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isPending): ?>
            <?php echo e(__('New event — review before creating')); ?>

        <?php elseif($status === 'created'): ?>
            <span style="color: var(--state-success);"><?php echo e(__('Event created')); ?></span>
        <?php elseif($status === 'discarded'): ?>
            <?php echo e(__('Discarded')); ?>

        <?php elseif($status === 'failed'): ?>
            <span style="color: var(--state-error);"><?php echo e(__('Could not prepare event')); ?></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($status === 'failed'): ?>
        <div class="px-4 py-4">
            <ul class="list-disc space-y-1 pl-5 text-sm" style="color: var(--ink);">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $validationErrors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li><?php echo e($error); ?></li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ul>
        </div>
    <?php elseif($isPending): ?>
        
        <div class="space-y-3 px-4 py-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($validationErrors !== []): ?>
                <ul class="list-disc space-y-1 rounded-md border px-4 py-2 pl-8 text-sm"
                    style="border-color: var(--state-error); color: var(--state-error); background-color: rgba(220, 38, 38, 0.06);"
                    data-test="tool-call-validation-errors"
                >
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $validationErrors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <li><?php echo e($error); ?></li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </ul>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal9040acb37c44d40c6c7317a01c1eea55 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9040acb37c44d40c6c7317a01c1eea55 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.input','data' => ['label' => __('Title'),'wire:model' => ''.e($editKey).'.title','maxlength' => '200','dataTest' => 'event-card-title']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Title')),'wire:model' => ''.e($editKey).'.title','maxlength' => '200','data-test' => 'event-card-title']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9040acb37c44d40c6c7317a01c1eea55)): ?>
<?php $attributes = $__attributesOriginal9040acb37c44d40c6c7317a01c1eea55; ?>
<?php unset($__attributesOriginal9040acb37c44d40c6c7317a01c1eea55); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9040acb37c44d40c6c7317a01c1eea55)): ?>
<?php $component = $__componentOriginal9040acb37c44d40c6c7317a01c1eea55; ?>
<?php unset($__componentOriginal9040acb37c44d40c6c7317a01c1eea55); ?>
<?php endif; ?>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <?php if (isset($component)) { $__componentOriginal1151d2fc806665a5106cfd999b6670bf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1151d2fc806665a5106cfd999b6670bf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.date-picker','data' => ['name' => ''.e($editKey).'.start_at','label' => __('Start'),'withTime' => true,'testId' => 'event-card-start']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.date-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => ''.e($editKey).'.start_at','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Start')),'with-time' => true,'test-id' => 'event-card-start']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1151d2fc806665a5106cfd999b6670bf)): ?>
<?php $attributes = $__attributesOriginal1151d2fc806665a5106cfd999b6670bf; ?>
<?php unset($__attributesOriginal1151d2fc806665a5106cfd999b6670bf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1151d2fc806665a5106cfd999b6670bf)): ?>
<?php $component = $__componentOriginal1151d2fc806665a5106cfd999b6670bf; ?>
<?php unset($__componentOriginal1151d2fc806665a5106cfd999b6670bf); ?>
<?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal1151d2fc806665a5106cfd999b6670bf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1151d2fc806665a5106cfd999b6670bf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.date-picker','data' => ['name' => ''.e($editKey).'.end_at','label' => __('End'),'withTime' => true,'testId' => 'event-card-end']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.date-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => ''.e($editKey).'.end_at','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('End')),'with-time' => true,'test-id' => 'event-card-end']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1151d2fc806665a5106cfd999b6670bf)): ?>
<?php $attributes = $__attributesOriginal1151d2fc806665a5106cfd999b6670bf; ?>
<?php unset($__attributesOriginal1151d2fc806665a5106cfd999b6670bf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1151d2fc806665a5106cfd999b6670bf)): ?>
<?php $component = $__componentOriginal1151d2fc806665a5106cfd999b6670bf; ?>
<?php unset($__componentOriginal1151d2fc806665a5106cfd999b6670bf); ?>
<?php endif; ?>
            </div>

            <label class="flex items-center gap-2">
                <input
                    type="checkbox"
                    wire:model="<?php echo e($editKey); ?>.is_all_day"
                    class="size-4 rounded border-[var(--border)] bg-[var(--bg-deep)] text-brand-danube focus:ring-brand-danube"
                />
                <span class="text-sm" style="color: var(--ink);"><?php echo e(__('All-day event')); ?></span>
            </label>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($colors !== []): ?>
                <div class="space-y-1.5">
                    <span class="text-xs uppercase tracking-wide" style="color: var(--ink-dim);"><?php echo e(__('Color')); ?></span>
                    <div class="flex flex-wrap gap-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $colors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <button
                                type="button"
                                wire:click="$set('<?php echo e($editKey); ?>.color', '<?php echo e($color['value']); ?>')"
                                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'size-9 rounded-full border-2 transition focus:outline-none focus:ring-2 focus:ring-brand-danube',
                                    'border-[var(--ink)]' => $selectedColor === $color['value'],
                                    'border-transparent hover:border-[var(--ink-muted)]' => $selectedColor !== $color['value'],
                                ]); ?>"
                                style="background-color: var(<?php echo e($color['var']); ?>);"
                                aria-label="<?php echo e($color['label']); ?>"
                                data-test="event-card-color-<?php echo e($color['value']); ?>"
                            ></button>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal9040acb37c44d40c6c7317a01c1eea55 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9040acb37c44d40c6c7317a01c1eea55 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.input','data' => ['label' => __('Location'),'wire:model' => ''.e($editKey).'.location','placeholder' => __('Optional'),'maxlength' => '200']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Location')),'wire:model' => ''.e($editKey).'.location','placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Optional')),'maxlength' => '200']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9040acb37c44d40c6c7317a01c1eea55)): ?>
<?php $attributes = $__attributesOriginal9040acb37c44d40c6c7317a01c1eea55; ?>
<?php unset($__attributesOriginal9040acb37c44d40c6c7317a01c1eea55); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9040acb37c44d40c6c7317a01c1eea55)): ?>
<?php $component = $__componentOriginal9040acb37c44d40c6c7317a01c1eea55; ?>
<?php unset($__componentOriginal9040acb37c44d40c6c7317a01c1eea55); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginal4269bfd92fa85e2de78666dd35a6befa = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4269bfd92fa85e2de78666dd35a6befa = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.textarea','data' => ['label' => __('Description'),'wire:model' => ''.e($editKey).'.description','rows' => 3,'maxlength' => '5000']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Description')),'wire:model' => ''.e($editKey).'.description','rows' => 3,'maxlength' => '5000']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4269bfd92fa85e2de78666dd35a6befa)): ?>
<?php $attributes = $__attributesOriginal4269bfd92fa85e2de78666dd35a6befa; ?>
<?php unset($__attributesOriginal4269bfd92fa85e2de78666dd35a6befa); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4269bfd92fa85e2de78666dd35a6befa)): ?>
<?php $component = $__componentOriginal4269bfd92fa85e2de78666dd35a6befa; ?>
<?php unset($__componentOriginal4269bfd92fa85e2de78666dd35a6befa); ?>
<?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recurrences !== []): ?>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <?php if (isset($component)) { $__componentOriginalcafb2873943de6a347bbd054e3da5f1f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcafb2873943de6a347bbd054e3da5f1f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.select','data' => ['label' => __('Repeat'),'wire:model.live' => ''.e($editKey).'.recurrence_freq']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Repeat')),'wire:model.live' => ''.e($editKey).'.recurrence_freq']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recurrences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $freq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($freq['value']); ?>"><?php echo e($freq['label']); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcafb2873943de6a347bbd054e3da5f1f)): ?>
<?php $attributes = $__attributesOriginalcafb2873943de6a347bbd054e3da5f1f; ?>
<?php unset($__attributesOriginalcafb2873943de6a347bbd054e3da5f1f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcafb2873943de6a347bbd054e3da5f1f)): ?>
<?php $component = $__componentOriginalcafb2873943de6a347bbd054e3da5f1f; ?>
<?php unset($__componentOriginalcafb2873943de6a347bbd054e3da5f1f); ?>
<?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedRecurrence !== 'none'): ?>
                        <?php if (isset($component)) { $__componentOriginal1151d2fc806665a5106cfd999b6670bf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1151d2fc806665a5106cfd999b6670bf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.date-picker','data' => ['name' => ''.e($editKey).'.recurrence_until','label' => __('Repeat until'),'testId' => 'event-card-recurrence-until']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.date-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => ''.e($editKey).'.recurrence_until','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Repeat until')),'test-id' => 'event-card-recurrence-until']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1151d2fc806665a5106cfd999b6670bf)): ?>
<?php $attributes = $__attributesOriginal1151d2fc806665a5106cfd999b6670bf; ?>
<?php unset($__attributesOriginal1151d2fc806665a5106cfd999b6670bf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1151d2fc806665a5106cfd999b6670bf)): ?>
<?php $component = $__componentOriginal1151d2fc806665a5106cfd999b6670bf; ?>
<?php unset($__componentOriginal1151d2fc806665a5106cfd999b6670bf); ?>
<?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php else: ?>
        
        <div class="space-y-3 px-4 py-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($args['title'])): ?>
                <div class="font-medium text-[15px]" style="color: var(--ink);"><?php echo e($args['title']); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($args['description'])): ?>
                <p class="whitespace-pre-wrap text-sm" style="color: var(--ink-muted);"><?php echo e($args['description']); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="grid grid-cols-1 gap-x-6 gap-y-2 text-sm md:grid-cols-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($args['start_at'])): ?>
                    <div class="flex items-center gap-2">
                        <span class="shrink-0 text-xs uppercase tracking-wide" style="color: var(--ink-dim);"><?php echo e(__('Start')); ?></span>
                        <span style="color: var(--ink);"><?php echo e($args['start_at']); ?></span>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($args['end_at'])): ?>
                    <div class="flex items-center gap-2">
                        <span class="shrink-0 text-xs uppercase tracking-wide" style="color: var(--ink-dim);"><?php echo e(__('End')); ?></span>
                        <span style="color: var(--ink);"><?php echo e($args['end_at']); ?></span>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($args['location'])): ?>
                    <div class="flex items-center gap-2">
                        <span class="shrink-0 text-xs uppercase tracking-wide" style="color: var(--ink-dim);"><?php echo e(__('Location')); ?></span>
                        <span style="color: var(--ink);"><?php echo e($args['location']); ?></span>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($args['recurrence_freq']) && $args['recurrence_freq'] !== 'none'): ?>
                    <div class="flex items-center gap-2">
                        <span class="shrink-0 text-xs uppercase tracking-wide" style="color: var(--ink-dim);"><?php echo e(__('Repeat')); ?></span>
                        <span style="color: var(--ink);"><?php echo e($args['recurrence_freq']); ?></span>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div
        class="flex flex-wrap items-center justify-end gap-2 border-t px-4 py-3"
        style="border-color: var(--border);"
    >
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isPending): ?>
            <button
                type="button"
                wire:click="discardToolCall(<?php echo e($message->id); ?>)"
                wire:loading.attr="disabled"
                class="rounded-full border px-4 py-1.5 text-sm transition"
                style="border-color: var(--border-mid); color: var(--ink-muted); background-color: transparent;"
                onmouseover="this.style.color='var(--ink)'; this.style.borderColor='var(--border-strong)';"
                onmouseout="this.style.color='var(--ink-muted)'; this.style.borderColor='var(--border-mid)';"
                data-test="discard-tool-call"
            >
                <?php echo e(__('Discard')); ?>

            </button>
            <button
                type="button"
                wire:click="confirmToolCall(<?php echo e($message->id); ?>)"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 rounded-full px-4 py-1.5 text-sm font-medium transition"
                style="background-color: var(--blue); color: white;"
                onmouseover="this.style.opacity='0.9'"
                onmouseout="this.style.opacity='1'"
                data-test="confirm-tool-call"
            >
                <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'check','class' => 'size-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','class' => 'size-4']); ?>
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
                <?php echo e(__('Create event')); ?>

            </button>
        <?php elseif($status === 'created'): ?>
            <a
                href="<?php echo e(route('calendar')); ?>"
                class="inline-flex items-center gap-1.5 rounded-full border px-4 py-1.5 text-sm transition"
                style="border-color: var(--border-mid); color: var(--ink); background-color: transparent;"
                onmouseover="this.style.borderColor='var(--border-strong)';"
                onmouseout="this.style.borderColor='var(--border-mid)';"
            >
                <?php echo e(__('Open in calendar')); ?>

                <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'arrow-up-right','class' => 'size-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-up-right','class' => 'size-3.5']); ?>
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
            </a>
        <?php elseif($status === 'discarded'): ?>
            <span class="text-xs italic" style="color: var(--ink-dim);"><?php echo e(__('No event was created.')); ?></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/chat/partials/tool-cards/create-event.blade.php ENDPATH**/ ?>