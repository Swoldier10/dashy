<?php
    use App\Domains\Calendar\Enums\EventColor;
    use App\Domains\Calendar\Enums\RecurrenceFreq;

    $colors = [
        EventColor::Danube,
        EventColor::Torea,
        EventColor::Shilo,
        EventColor::Success,
        EventColor::Warning,
        EventColor::Error,
    ];

    $recurrenceOptions = [
        RecurrenceFreq::None,
        RecurrenceFreq::Daily,
        RecurrenceFreq::Weekly,
        RecurrenceFreq::Monthly,
        RecurrenceFreq::Yearly,
    ];

    $isCreate = $drawerCreateMode;
?>

<?php if (isset($component)) { $__componentOriginal7b48427f97b7da498a55d72398ac1330 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7b48427f97b7da498a55d72398ac1330 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.drawer','data' => ['name' => 'calendar-event-detail','side' => 'right','size' => 'md','focusable' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.drawer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'calendar-event-detail','side' => 'right','size' => 'md','focusable' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div
        <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'event-drawer-'.e($detailEventId ?? 'new').'-'.e($isCreate ? 'create' : 'edit').''; ?>wire:key="event-drawer-<?php echo e($detailEventId ?? 'new'); ?>-<?php echo e($isCreate ? 'create' : 'edit'); ?>"
        class="flex h-full flex-col"
    >
        <form
            wire:submit.prevent="<?php echo e($isCreate ? 'submitCreate' : 'submitEdit'); ?>"
            class="flex h-full flex-col gap-5 p-6"
            data-test="calendar-event-form"
        >
            <header class="flex items-start justify-between gap-3">
                <h2 class="font-display text-xl text-[var(--ink)]">
                    <?php echo e($isCreate ? __('New event') : __('Edit event')); ?>

                </h2>
                <button
                    type="button"
                    @click="$store.modals.close('calendar-event-detail')"
                    class="inline-flex size-9 items-center justify-center rounded-md text-[var(--ink-muted)] hover:bg-[var(--surface-2)] hover:text-[var(--ink)]"
                    aria-label="<?php echo e(__('Close')); ?>"
                >
                    <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => 'x-mark','class' => 'size-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'x-mark','class' => 'size-5']); ?>
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
            </header>

            <?php if (isset($component)) { $__componentOriginal9040acb37c44d40c6c7317a01c1eea55 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9040acb37c44d40c6c7317a01c1eea55 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.input','data' => ['wire:model.live' => 'formTitle','name' => 'formTitle','label' => __('Title'),'placeholder' => __('What is happening?'),'maxlength' => '200','required' => true,'dataTest' => 'calendar-event-title']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'formTitle','name' => 'formTitle','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Title')),'placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('What is happening?')),'maxlength' => '200','required' => true,'data-test' => 'calendar-event-title']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.date-picker','data' => ['wire:model.live' => 'formStartAt','name' => 'formStartAt','label' => __('Start'),'withTime' => true,'testId' => 'calendar-event-start']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.date-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'formStartAt','name' => 'formStartAt','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Start')),'with-time' => true,'test-id' => 'calendar-event-start']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.date-picker','data' => ['wire:model.live' => 'formEndAt','name' => 'formEndAt','label' => __('End'),'withTime' => true,'testId' => 'calendar-event-end']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.date-picker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'formEndAt','name' => 'formEndAt','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('End')),'with-time' => true,'test-id' => 'calendar-event-end']); ?>
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
                    wire:model.live="formIsAllDay"
                    class="size-4 rounded border-[var(--border)] bg-[var(--bg-deep)] text-brand-danube focus:ring-brand-danube"
                />
                <span class="text-sm text-[var(--ink)]"><?php echo e(__('All-day event')); ?></span>
            </label>

            <div class="grid gap-2">
                <span class="text-xs font-medium uppercase tracking-wider text-[var(--ink-dim)]"><?php echo e(__('Color')); ?></span>
                <div class="flex flex-wrap gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $colors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <button
                            type="button"
                            wire:click="$set('formColor', '<?php echo e($color->value); ?>')"
                            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'size-9 rounded-full border-2 transition focus:outline-none focus:ring-2 focus:ring-brand-danube',
                                'border-[var(--ink)]' => $formColor === $color->value,
                                'border-transparent hover:border-[var(--ink-muted)]' => $formColor !== $color->value,
                            ]); ?>"
                            style="background-color: var(<?php echo e($color->colorVar()); ?>);"
                            aria-label="<?php echo e($color->label()); ?>"
                            data-test="calendar-color-<?php echo e($color->value); ?>"
                        ></button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>

            <?php if (isset($component)) { $__componentOriginal9040acb37c44d40c6c7317a01c1eea55 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9040acb37c44d40c6c7317a01c1eea55 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.input','data' => ['wire:model.live' => 'formLocation','name' => 'formLocation','label' => __('Location'),'placeholder' => __('Optional'),'maxlength' => '200']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'formLocation','name' => 'formLocation','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Location')),'placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Optional')),'maxlength' => '200']); ?>
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

            <label class="grid gap-1.5">
                <span class="text-xs font-medium uppercase tracking-wider text-[var(--ink-dim)]"><?php echo e(__('Description')); ?></span>
                <textarea
                    wire:model.live="formDescription"
                    rows="3"
                    class="rounded-md border border-[var(--border)] bg-[var(--bg-deep)] p-3 text-sm text-[var(--ink)] focus:border-brand-danube focus:outline-none"
                ></textarea>
            </label>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <label class="grid gap-1.5">
                    <span class="text-xs font-medium uppercase tracking-wider text-[var(--ink-dim)]"><?php echo e(__('Repeat')); ?></span>
                    <select
                        wire:model.live="formRecurrenceFreq"
                        class="h-11 rounded-md border border-[var(--border)] bg-[var(--bg-deep)] px-3 text-sm text-[var(--ink)] focus:border-brand-danube focus:outline-none lg:h-9"
                    >
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recurrenceOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($option->value); ?>"><?php echo e($option->label()); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </label>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($formRecurrenceFreq !== 'none'): ?>
                    <label class="grid gap-1.5">
                        <span class="text-xs font-medium uppercase tracking-wider text-[var(--ink-dim)]"><?php echo e(__('Repeat until')); ?></span>
                        <input
                            type="date"
                            wire:model.live="formRecurrenceUntil"
                            class="h-11 rounded-md border border-[var(--border)] bg-[var(--bg-deep)] px-3 text-sm text-[var(--ink)] focus:border-brand-danube focus:outline-none lg:h-9"
                            data-test="calendar-event-recurrence-until"
                        />
                    </label>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <footer class="mt-auto flex flex-wrap items-center justify-between gap-3 pt-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $isCreate): ?>
                    <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['type' => 'button','wire:click' => 'deleteEvent','wire:confirm' => ''.e(__('Delete this event?')).'','variant' => 'ghost','icon' => 'trash','dataTest' => 'calendar-event-delete']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','wire:click' => 'deleteEvent','wire:confirm' => ''.e(__('Delete this event?')).'','variant' => 'ghost','icon' => 'trash','data-test' => 'calendar-event-delete']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <?php echo e(__('Delete')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba060e0bacbfaf03558d70b3da7edee1)): ?>
<?php $attributes = $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1; ?>
<?php unset($__attributesOriginalba060e0bacbfaf03558d70b3da7edee1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba060e0bacbfaf03558d70b3da7edee1)): ?>
<?php $component = $__componentOriginalba060e0bacbfaf03558d70b3da7edee1; ?>
<?php unset($__componentOriginalba060e0bacbfaf03558d70b3da7edee1); ?>
<?php endif; ?>
                <?php else: ?>
                    <span></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="flex items-center gap-2">
                    <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['type' => 'button','@click' => '$store.modals.close(\'calendar-event-detail\')','variant' => 'ghost']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','@click' => '$store.modals.close(\'calendar-event-detail\')','variant' => 'ghost']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <?php echo e(__('Cancel')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba060e0bacbfaf03558d70b3da7edee1)): ?>
<?php $attributes = $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1; ?>
<?php unset($__attributesOriginalba060e0bacbfaf03558d70b3da7edee1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba060e0bacbfaf03558d70b3da7edee1)): ?>
<?php $component = $__componentOriginalba060e0bacbfaf03558d70b3da7edee1; ?>
<?php unset($__componentOriginalba060e0bacbfaf03558d70b3da7edee1); ?>
<?php endif; ?>

                    <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['type' => 'submit','variant' => 'primary','dataTest' => 'calendar-event-submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','variant' => 'primary','data-test' => 'calendar-event-submit']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <?php echo e($isCreate ? __('Create') : __('Save')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba060e0bacbfaf03558d70b3da7edee1)): ?>
<?php $attributes = $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1; ?>
<?php unset($__attributesOriginalba060e0bacbfaf03558d70b3da7edee1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba060e0bacbfaf03558d70b3da7edee1)): ?>
<?php $component = $__componentOriginalba060e0bacbfaf03558d70b3da7edee1; ?>
<?php unset($__componentOriginalba060e0bacbfaf03558d70b3da7edee1); ?>
<?php endif; ?>
                </div>
            </footer>
        </form>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7b48427f97b7da498a55d72398ac1330)): ?>
<?php $attributes = $__attributesOriginal7b48427f97b7da498a55d72398ac1330; ?>
<?php unset($__attributesOriginal7b48427f97b7da498a55d72398ac1330); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7b48427f97b7da498a55d72398ac1330)): ?>
<?php $component = $__componentOriginal7b48427f97b7da498a55d72398ac1330; ?>
<?php unset($__componentOriginal7b48427f97b7da498a55d72398ac1330); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/livewire/calendar/partials/event-drawer.blade.php ENDPATH**/ ?>