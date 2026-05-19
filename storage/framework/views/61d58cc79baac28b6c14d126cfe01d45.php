<?php
    /**
     * @var \App\Domains\Chat\Models\Message $message
     * @var array<string, mixed>|null $card
     */
    $name = $card['name'] ?? null;
    $mode = $card['mode'] ?? null;
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($card === null): ?>
<?php elseif($mode === 'auto_read'): ?>
    <?php echo $__env->make('livewire.chat.partials.tool-cards.read-pill', ['message' => $message, 'card' => $card], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php elseif($mode === 'compact_write'): ?>
    <?php echo $__env->make('livewire.chat.partials.tool-cards.compact-write', ['message' => $message, 'card' => $card], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php elseif($mode === 'bulk_write'): ?>
    <?php echo $__env->make('livewire.chat.partials.tool-cards.bulk-write', ['message' => $message, 'card' => $card], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php elseif($mode === 'structural_auto'): ?>
    <?php echo $__env->make('livewire.chat.partials.tool-cards.plan', ['message' => $message, 'card' => $card], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php elseif($name === 'create_task'): ?>
    <?php echo $__env->make('livewire.chat.partials.tool-cards.create-task', ['message' => $message, 'card' => $card], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php elseif($name === 'create_event'): ?>
    <?php echo $__env->make('livewire.chat.partials.tool-cards.create-event', ['message' => $message, 'card' => $card], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php elseif($name === 'create_project'): ?>
    <?php echo $__env->make('livewire.chat.partials.tool-cards.create-project', ['message' => $message, 'card' => $card], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php elseif($name === 'ask_user_choice'): ?>
    <?php echo $__env->make('livewire.chat.partials.tool-cards.ask-user-choice', ['message' => $message, 'card' => $card], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH /var/www/html/resources/views/livewire/chat/partials/tool-call-card.blade.php ENDPATH**/ ?>