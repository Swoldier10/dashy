<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="dark">
<head>
    <?php echo $__env->make('partials.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body class="dashy-body min-h-dvh antialiased">
    <div class="relative min-h-dvh overflow-hidden flex flex-col">

        
        <div aria-hidden="true" class="dashy-mark">d</div>

        
        <header class="relative z-10 flex items-center justify-between px-7 sm:px-10 lg:px-14 py-7">
            <a href="<?php echo e(route('home')); ?>" wire:navigate class="flex items-center gap-2.5 group">
                <span class="dashy-logo-mark">
                    <svg viewBox="0 0 22 22" fill="none" class="size-[22px]">
                        <rect x="2.25" y="2.25" width="7.5" height="7.5" rx="1.6" stroke="currentColor" stroke-width="1.6"/>
                        <rect x="12.25" y="2.25" width="7.5" height="7.5" rx="1.6" stroke="currentColor" stroke-width="1.6"/>
                        <rect x="2.25" y="12.25" width="7.5" height="7.5" rx="1.6" stroke="currentColor" stroke-width="1.6"/>
                        <rect x="12.25" y="12.25" width="7.5" height="7.5" rx="1.6" stroke="currentColor" stroke-width="1.6"/>
                    </svg>
                </span>
                <span class="text-[15px] font-medium tracking-tight text-[var(--ink)]">dashy</span>
            </a>

            <nav class="flex items-center gap-7 text-sm">
                <?php echo e($headerNav ?? ''); ?>

            </nav>
        </header>

        
        <main class="relative z-10 flex-1 mx-auto w-full max-w-[1440px] px-7 sm:px-10 lg:px-14">
            <?php echo e($slot); ?>

        </main>

        
        <footer class="relative z-10 px-7 sm:px-10 lg:px-14 py-7 mt-12">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs text-[var(--ink-dim)]">
                <div>© <?php echo e(date('Y')); ?> Dashy Labs · San Francisco</div>
                <div class="flex items-center gap-6">
                    <a href="#" class="hover:text-[var(--ink-muted)] transition-colors">Privacy</a>
                    <span aria-hidden="true">·</span>
                    <a href="#" class="hover:text-[var(--ink-muted)] transition-colors">Terms</a>
                    <span aria-hidden="true">·</span>
                    <a href="#" class="hover:text-[var(--ink-muted)] transition-colors">DPA</a>
                </div>
            </div>
        </footer>
    </div>

    <?php app("livewire")->forceAssetInjection(); ?><div x-persist="<?php echo e('toast'); ?>">
        <?php if (isset($component)) { $__componentOriginalbffee1913b3b1de42f0013fe2cab11c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbffee1913b3b1de42f0013fe2cab11c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.toaster','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.toaster'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbffee1913b3b1de42f0013fe2cab11c1)): ?>
<?php $attributes = $__attributesOriginalbffee1913b3b1de42f0013fe2cab11c1; ?>
<?php unset($__attributesOriginalbffee1913b3b1de42f0013fe2cab11c1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbffee1913b3b1de42f0013fe2cab11c1)): ?>
<?php $component = $__componentOriginalbffee1913b3b1de42f0013fe2cab11c1; ?>
<?php unset($__componentOriginalbffee1913b3b1de42f0013fe2cab11c1); ?>
<?php endif; ?>
    </div>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/layouts/auth/dashy.blade.php ENDPATH**/ ?>