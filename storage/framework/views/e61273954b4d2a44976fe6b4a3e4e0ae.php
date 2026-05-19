<?php
use App\Domains\Preferences\Services\ForgetMemoryService;
use App\Domains\Preferences\Services\ListMemoriesService;
use App\Support\Concerns\DispatchesDashyUi;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
?>

<div>
    <section class="dashy-settings-section">
        <div class="dashy-settings-section-head">
            <h3><?php echo e(__('Memory')); ?></h3>
            <p><?php echo e(__('Facts the assistant has saved about you. These are surfaced into every future chat session.')); ?></p>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($this->memories) === 0): ?>
            <div class="dashy-settings-row" data-test="memory-empty">
                <div class="dashy-settings-row-label">
                    <span class="row-label-text"><?php echo e(__('No memories yet')); ?></span>
                    <span class="row-label-desc"><?php echo e(__('When you tell the assistant to remember something, it will appear here.')); ?></span>
                </div>
            </div>
        <?php else: ?>
            <ul class="flex flex-col" data-test="memory-list">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->memories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $memory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li class="dashy-settings-row" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'memory-'.e($memory['key']).''; ?>wire:key="memory-<?php echo e($memory['key']); ?>">
                        <div class="dashy-settings-row-label min-w-0">
                            <span class="row-label-text"><?php echo e($memory['fact']); ?></span>
                            <span class="row-label-desc">
                                <span class="font-mono"><?php echo e($memory['key']); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($memory['created_at'])): ?>
                                    · <?php echo e(\Illuminate\Support\Carbon::parse($memory['created_at'])->diffForHumans()); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </span>
                        </div>
                        <div class="dashy-settings-row-value flex justify-start sm:justify-end">
                            <button
                                type="button"
                                wire:click="forget('<?php echo e($memory['key']); ?>')"
                                class="dashy-btn dashy-btn--sm"
                                style="color: var(--state-error); border-color: var(--border-mid); background-color: transparent;"
                                data-test="forget-<?php echo e($memory['key']); ?>"
                            >
                                <?php echo e(__('Forget')); ?>

                            </button>
                        </div>
                    </li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ul>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </section>
</div><?php /**PATH /var/www/html/storage/framework/views/livewire/views/9ba0eac1.blade.php ENDPATH**/ ?>