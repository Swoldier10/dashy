<?php
    /**
     * @var \App\Domains\Chat\Models\Message $message
     * @var array<string, mixed> $card
     *
     * Compact ghost-pill rendering for auto_read tool calls. The actual data
     * went to the LLM via function_call_output — this view just tells the
     * user what was looked up, so the agentic loop stays transparent.
     */
    $status = $card['status'] ?? 'executed';
    $label = (string) ($card['label'] ?? __('Lookup'));
    $icon = (string) ($card['icon'] ?? 'magnifying-glass');
    $count = $card['count'] ?? null;
    $isFailed = $status === 'failed';
    $isExecuted = $status === 'executed';
    $color = $isFailed ? 'var(--state-error)' : 'var(--ink-muted)';
?>

<div
    class="mt-2 inline-flex max-w-full items-center gap-2 rounded-full border px-3 py-1.5 text-xs sm:text-[13px]"
    style="border-color: var(--border-mid); background-color: var(--surface-2); color: <?php echo e($color); ?>;"
    data-test="tool-call-card"
    data-tool="<?php echo e($card['name'] ?? ''); ?>"
    data-mode="auto_read"
    data-status="<?php echo e($status); ?>"
>
    <?php if (isset($component)) { $__componentOriginal95d44a2f66f034299285b9491205706f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95d44a2f66f034299285b9491205706f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.icon','data' => ['name' => $isFailed ? 'exclamation-triangle' : $icon,'class' => 'size-3.5 shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($isFailed ? 'exclamation-triangle' : $icon),'class' => 'size-3.5 shrink-0']); ?>
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
    <span class="truncate"><?php echo e($label); ?></span>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $isFailed && $isExecuted): ?>
        <span aria-hidden="true" style="color: var(--state-success);">✓</span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/chat/partials/tool-cards/read-pill.blade.php ENDPATH**/ ?>