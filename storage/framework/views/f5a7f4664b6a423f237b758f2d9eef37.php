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
<?php /**PATH /var/www/html/resources/views/livewire/chat/partials/empty-greeting.blade.php ENDPATH**/ ?>