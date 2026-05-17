<?php
    /**
     * Per-project rendering — the project pill is omitted (you're already
     * inside the project), the status pill is shown inline. The aggregator
     * page includes `task-row-card` directly with different flags.
     */
?>

<?php echo $__env->make('livewire.tasks.partials.task-row-card', [
    'task' => $task,
    'teamMembers' => $teamMembers,
    'allStatuses' => $allStatuses,
    'showProjectPill' => false,
    'showStatusPill' => true,
    'showCheckbox' => true,
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH /var/www/html/resources/views/livewire/tasks/partials/task-row.blade.php ENDPATH**/ ?>