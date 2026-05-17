<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <?php echo $__env->make('partials.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </head>
    <body class="dashy-body min-h-screen">
        <div class="flex min-h-screen flex-col lg:flex-row">
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('app-sidebar', []);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-216690657-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
            <main class="flex min-w-0 flex-1 flex-col pb-24 md:pb-20">
                <?php echo e($slot); ?>

            </main>
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

        
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('codex.connect-codex-modal', []);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-216690657-1', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>

        
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('settings-modal', []);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-216690657-2', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>

        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('time-tracking.running-timer-pill', []);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-216690657-3', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>

        <?php if (isset($component)) { $__componentOriginal2bf8373349a2037d0796e3c98d320f79 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2bf8373349a2037d0796e3c98d320f79 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.bottom-tab-bar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.bottom-tab-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2bf8373349a2037d0796e3c98d320f79)): ?>
<?php $attributes = $__attributesOriginal2bf8373349a2037d0796e3c98d320f79; ?>
<?php unset($__attributesOriginal2bf8373349a2037d0796e3c98d320f79); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2bf8373349a2037d0796e3c98d320f79)): ?>
<?php $component = $__componentOriginal2bf8373349a2037d0796e3c98d320f79; ?>
<?php unset($__componentOriginal2bf8373349a2037d0796e3c98d320f79); ?>
<?php endif; ?>
    </body>
</html>
<?php /**PATH /var/www/html/resources/views/layouts/app/shell.blade.php ENDPATH**/ ?>