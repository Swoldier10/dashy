<div>
    <?php if (isset($component)) { $__componentOriginal6f3ea2574b5a945d549c436557b459c9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6f3ea2574b5a945d549c436557b459c9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.modal','data' => ['name' => 'connect-codex','focusable' => true,'class' => 'max-w-md','wire:close' => 'cancel']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'connect-codex','focusable' => true,'class' => 'max-w-md','wire:close' => 'cancel']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div
            class="space-y-5"
            x-data="{
                interval: null,
                start() {
                    if (this.interval) { return; }
                    this.interval = setInterval(() => $wire.poll(), <?php echo e($pollIntervalMs); ?>);
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
                <?php if (isset($component)) { $__componentOriginal0c6359c35515883081bfd9ec3f253da0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0c6359c35515883081bfd9ec3f253da0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.heading','data' => ['size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.heading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'lg']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Connect Codex')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0c6359c35515883081bfd9ec3f253da0)): ?>
<?php $attributes = $__attributesOriginal0c6359c35515883081bfd9ec3f253da0; ?>
<?php unset($__attributesOriginal0c6359c35515883081bfd9ec3f253da0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0c6359c35515883081bfd9ec3f253da0)): ?>
<?php $component = $__componentOriginal0c6359c35515883081bfd9ec3f253da0; ?>
<?php unset($__componentOriginal0c6359c35515883081bfd9ec3f253da0); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginale626700ad092668e460de4abfec60854 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale626700ad092668e460de4abfec60854 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.subheading','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.subheading'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <?php echo e(__('Open the link below, sign in to your ChatGPT account and enter the one-time code.')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale626700ad092668e460de4abfec60854)): ?>
<?php $attributes = $__attributesOriginale626700ad092668e460de4abfec60854; ?>
<?php unset($__attributesOriginale626700ad092668e460de4abfec60854); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale626700ad092668e460de4abfec60854)): ?>
<?php $component = $__componentOriginale626700ad092668e460de4abfec60854; ?>
<?php unset($__componentOriginale626700ad092668e460de4abfec60854); ?>
<?php endif; ?>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($userCode !== null): ?>
                <div class="space-y-3 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <?php if (isset($component)) { $__componentOriginald9fee6ad637afd79ed43645955ec1b38 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald9fee6ad637afd79ed43645955ec1b38 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.text','data' => ['variant' => 'subtle','class' => 'text-xs uppercase tracking-wide']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.text'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'subtle','class' => 'text-xs uppercase tracking-wide']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Step 1 — open this URL')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald9fee6ad637afd79ed43645955ec1b38)): ?>
<?php $attributes = $__attributesOriginald9fee6ad637afd79ed43645955ec1b38; ?>
<?php unset($__attributesOriginald9fee6ad637afd79ed43645955ec1b38); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald9fee6ad637afd79ed43645955ec1b38)): ?>
<?php $component = $__componentOriginald9fee6ad637afd79ed43645955ec1b38; ?>
<?php unset($__componentOriginald9fee6ad637afd79ed43645955ec1b38); ?>
<?php endif; ?>
                    <a href="<?php echo e($verificationUrl); ?>" target="_blank" rel="noopener" class="break-all text-sm underline">
                        <?php echo e($verificationUrl); ?>

                    </a>

                    <?php if (isset($component)) { $__componentOriginal2ee2322463ab76e015442fd865254c9a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ee2322463ab76e015442fd865254c9a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.separator','data' => ['variant' => 'subtle']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.separator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'subtle']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ee2322463ab76e015442fd865254c9a)): ?>
<?php $attributes = $__attributesOriginal2ee2322463ab76e015442fd865254c9a; ?>
<?php unset($__attributesOriginal2ee2322463ab76e015442fd865254c9a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ee2322463ab76e015442fd865254c9a)): ?>
<?php $component = $__componentOriginal2ee2322463ab76e015442fd865254c9a; ?>
<?php unset($__componentOriginal2ee2322463ab76e015442fd865254c9a); ?>
<?php endif; ?>

                    <?php if (isset($component)) { $__componentOriginald9fee6ad637afd79ed43645955ec1b38 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald9fee6ad637afd79ed43645955ec1b38 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.text','data' => ['variant' => 'subtle','class' => 'text-xs uppercase tracking-wide']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.text'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'subtle','class' => 'text-xs uppercase tracking-wide']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<?php echo e(__('Step 2 — enter this code')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald9fee6ad637afd79ed43645955ec1b38)): ?>
<?php $attributes = $__attributesOriginald9fee6ad637afd79ed43645955ec1b38; ?>
<?php unset($__attributesOriginald9fee6ad637afd79ed43645955ec1b38); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald9fee6ad637afd79ed43645955ec1b38)): ?>
<?php $component = $__componentOriginald9fee6ad637afd79ed43645955ec1b38; ?>
<?php unset($__componentOriginald9fee6ad637afd79ed43645955ec1b38); ?>
<?php endif; ?>
                    <div class="select-all rounded-md bg-zinc-100 p-3 text-center font-mono text-2xl tracking-[0.3em] dark:bg-zinc-800">
                        <?php echo e($userCode); ?>

                    </div>

                    <?php if (isset($component)) { $__componentOriginald9fee6ad637afd79ed43645955ec1b38 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald9fee6ad637afd79ed43645955ec1b38 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.text','data' => ['variant' => 'subtle','class' => 'text-xs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.text'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'subtle','class' => 'text-xs']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <?php echo e(__('Waiting for you to approve…')); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald9fee6ad637afd79ed43645955ec1b38)): ?>
<?php $attributes = $__attributesOriginald9fee6ad637afd79ed43645955ec1b38; ?>
<?php unset($__attributesOriginald9fee6ad637afd79ed43645955ec1b38); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald9fee6ad637afd79ed43645955ec1b38)): ?>
<?php $component = $__componentOriginald9fee6ad637afd79ed43645955ec1b38; ?>
<?php unset($__componentOriginald9fee6ad637afd79ed43645955ec1b38); ?>
<?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="flex justify-end">
                <?php if (isset($component)) { $__componentOriginalba060e0bacbfaf03558d70b3da7edee1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba060e0bacbfaf03558d70b3da7edee1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.button','data' => ['type' => 'button','variant' => 'filled','wire:click' => 'cancel','dataTest' => 'cancel-connect-codex']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'filled','wire:click' => 'cancel','data-test' => 'cancel-connect-codex']); ?>
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
            </div>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6f3ea2574b5a945d549c436557b459c9)): ?>
<?php $attributes = $__attributesOriginal6f3ea2574b5a945d549c436557b459c9; ?>
<?php unset($__attributesOriginal6f3ea2574b5a945d549c436557b459c9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6f3ea2574b5a945d549c436557b459c9)): ?>
<?php $component = $__componentOriginal6f3ea2574b5a945d549c436557b459c9; ?>
<?php unset($__componentOriginal6f3ea2574b5a945d549c436557b459c9); ?>
<?php endif; ?>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/codex/connect-codex-modal.blade.php ENDPATH**/ ?>