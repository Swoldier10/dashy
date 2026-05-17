<?php
    use App\Domains\Projects\Support\ProjectColor;
    use App\Domains\Projects\Support\ProjectIconShape;

    /** @var \App\Domains\Projects\Models\Project $project */
    $size = $size ?? 'sm'; // xs | sm | md
    $dimensions = match ($size) {
        'xs' => 14,
        'md' => 20,
        default => 16,
    };
    $colorVar = ProjectColor::for($project);
    $shape = ProjectIconShape::for($project);
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($project->logo): ?>
    <img
        src="<?php echo e($project->logo); ?>"
        alt=""
        class="shrink-0 rounded object-cover"
        style="width: <?php echo e($dimensions); ?>px; height: <?php echo e($dimensions); ?>px;"
    />
<?php else: ?>
    <span
        class="inline-flex shrink-0 items-center justify-center"
        style="width: <?php echo e($dimensions); ?>px; height: <?php echo e($dimensions); ?>px;"
        aria-hidden="true"
    >
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($shape === ProjectIconShape::CIRCLE): ?>
            <span class="inline-block rounded-full"
                  style="width: <?php echo e($dimensions - 4); ?>px; height: <?php echo e($dimensions - 4); ?>px; background-color: var(<?php echo e($colorVar); ?>);"></span>
        <?php elseif($shape === ProjectIconShape::TRIANGLE): ?>
            <svg viewBox="0 0 10 10" width="<?php echo e($dimensions - 2); ?>" height="<?php echo e($dimensions - 2); ?>" fill="var(<?php echo e($colorVar); ?>)">
                <polygon points="5,1 9,9 1,9"></polygon>
            </svg>
        <?php else: ?>
            <svg viewBox="0 0 12 12" width="<?php echo e($dimensions); ?>" height="<?php echo e($dimensions); ?>" fill="none"
                 stroke="var(<?php echo e($colorVar); ?>)" stroke-width="2" stroke-linecap="round">
                <line x1="6" y1="2" x2="6" y2="10"></line>
                <line x1="2" y1="6" x2="10" y2="6"></line>
            </svg>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </span>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH /var/www/html/resources/views/livewire/tasks/partials/project-shape.blade.php ENDPATH**/ ?>