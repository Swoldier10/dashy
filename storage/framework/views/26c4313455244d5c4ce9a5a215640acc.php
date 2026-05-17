<?php
    /**
     * @var array<int, array{key:string,label:string,count:int,colorVar:string,anchor:string}> $buckets
     * @var int $openCount
     * @var int $doneCount
     */
    $buckets = $buckets ?? [];
    $openCount = $openCount ?? 0;
    $doneCount = $doneCount ?? 0;
?>

<div class="flex flex-wrap items-center gap-2" data-test="status-summary">
    <div class="flex flex-wrap items-center gap-1 sm:gap-1.5">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $buckets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bucket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <span
                <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'summary-'.e($bucket['key']).''; ?>wire:key="summary-<?php echo e($bucket['key']); ?>"
                class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-[11px] font-medium"
                style="
                    background-color: color-mix(in srgb, var(<?php echo e($bucket['colorVar']); ?>) 14%, transparent);
                    color: color-mix(in srgb, var(<?php echo e($bucket['colorVar']); ?>) 80%, var(--ink));
                "
                data-test="status-summary-<?php echo e($bucket['key']); ?>"
            >
                <span class="inline-block size-1 rounded-full" style="background-color: var(<?php echo e($bucket['colorVar']); ?>);"></span>
                <span><?php echo e($bucket['label']); ?></span>
                <span style="color: color-mix(in srgb, var(<?php echo e($bucket['colorVar']); ?>) 70%, var(--ink-dim));"><?php echo e($bucket['count']); ?></span>
            </span>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>

    <div class="ml-auto whitespace-nowrap text-[11px]" style="color: var(--ink-dim);" data-test="status-summary-totals">
        <span><span style="color: var(--ink-muted);"><?php echo e($openCount); ?></span> <?php echo e(__('open')); ?></span>
        <span class="mx-1">·</span>
        <span><span style="color: var(--ink-muted);"><?php echo e($doneCount); ?></span> <?php echo e(__('done')); ?></span>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/tasks/partials/status-summary-bar.blade.php ENDPATH**/ ?>