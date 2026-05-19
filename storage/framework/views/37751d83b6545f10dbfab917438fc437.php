<?php
use App\Domains\Auth\Enums\Salutation;
?>
<?php if (isset($component)) { $__componentOriginal694aac3c67d98e1abac25e51c700a4e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal694aac3c67d98e1abac25e51c700a4e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'f4ac99e09542ff494432bc959d4fee61::auth.dashy','data' => ['title' => __('Create account')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts::auth.dashy'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create account'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('headerNav', null, []); ?> 
        <span class="text-[var(--ink-muted)]">Already on Dashy?</span>
        <a href="<?php echo e(route('login')); ?>" wire:navigate class="dashy-header-link">Sign in</a>
     <?php $__env->endSlot(); ?>

    <div class="grid lg:grid-cols-12 items-start gap-12 lg:gap-20 pt-8 sm:pt-14 lg:pt-20 pb-20">

        
        <section class="lg:col-span-6 dashy-fade" style="--delay: 0ms">
            <div class="dashy-eyebrow mb-7">
                <span class="size-1.5 rounded-full bg-[var(--blue)] dashy-pulse-blue"></span>
                Create your Dashy account
            </div>

            <h1 class="dashy-headline dashy-headline--md">
                One workspace<br>
                for the whole<br>
                <em>operation.</em>
            </h1>

            <p class="mt-7 max-w-md text-[15px] leading-relaxed text-[var(--ink-muted)]">
                Tasks, docs, goals and chat — all in one place your team will actually open.
            </p>

            <div class="dashy-trust mt-12">
                <span>
                    <svg viewBox="0 0 16 16" fill="none" class="size-[14px]"><path d="M3 8.5l3.2 3.2L13 4.8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Free up to 5 seats
                </span>
                <span>
                    <svg viewBox="0 0 16 16" fill="none" class="size-[14px]"><path d="M3 8.5l3.2 3.2L13 4.8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    SOC 2 Type II
                </span>
                <span>
                    <svg viewBox="0 0 16 16" fill="none" class="size-[14px]"><path d="M3 8.5l3.2 3.2L13 4.8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Cancel anytime
                </span>
            </div>
        </section>

        
        <section class="lg:col-span-6 dashy-fade" style="--delay: 200ms">
            <div class="dashy-card" x-data="{ pw: '', strength() {
                    let s = 0;
                    if (this.pw.length >= 8) s++;
                    if (/[a-z]/.test(this.pw) && /[A-Z]/.test(this.pw)) s++;
                    if (/\d/.test(this.pw)) s++;
                    if (/[^a-zA-Z0-9]/.test(this.pw)) s++;
                    return s;
                } }">

                <?php if (isset($component)) { $__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth-session-status','data' => ['status' => session('status')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('auth-session-status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(session('status'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5)): ?>
<?php $attributes = $__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5; ?>
<?php unset($__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5)): ?>
<?php $component = $__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5; ?>
<?php unset($__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5); ?>
<?php endif; ?>

                <form method="POST" action="<?php echo e(route('register.store')); ?>" class="space-y-5">
                    <?php echo csrf_field(); ?>

                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label for="salutation" class="dashy-label">Salutation</label>
                            <div class="dashy-select-wrap">
                                <select
                                    id="salutation"
                                    name="salutation"
                                    class="dashy-select"
                                    x-data="{ v: '<?php echo e(old('salutation', '')); ?>' }"
                                    x-model="v"
                                    :data-empty="!v"
                                >
                                    <option value="" disabled><?php echo e(__('Select')); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = Salutation::options(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($value); ?>" <?php if(old('salutation') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <svg viewBox="0 0 16 16" fill="none" class="dashy-select-chevron size-3.5">
                                    <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <?php if (isset($component)) { $__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.field-error','data' => ['name' => 'salutation']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.field-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'salutation']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b)): ?>
<?php $attributes = $__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b; ?>
<?php unset($__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b)): ?>
<?php $component = $__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b; ?>
<?php unset($__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b); ?>
<?php endif; ?>
                        </div>

                        <div>
                            <label for="first_name" class="dashy-label">First name</label>
                            <input
                                id="first_name"
                                name="first_name"
                                type="text"
                                value="<?php echo e(old('first_name')); ?>"
                                required
                                autofocus
                                autocomplete="given-name"
                                placeholder="Lina"
                                class="dashy-input"
                            />
                            <?php if (isset($component)) { $__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.field-error','data' => ['name' => 'first_name']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.field-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'first_name']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b)): ?>
<?php $attributes = $__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b; ?>
<?php unset($__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b)): ?>
<?php $component = $__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b; ?>
<?php unset($__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b); ?>
<?php endif; ?>
                        </div>

                        <div>
                            <label for="last_name" class="dashy-label">Last name</label>
                            <input
                                id="last_name"
                                name="last_name"
                                type="text"
                                value="<?php echo e(old('last_name')); ?>"
                                required
                                autocomplete="family-name"
                                placeholder="Marsh"
                                class="dashy-input"
                            />
                            <?php if (isset($component)) { $__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.field-error','data' => ['name' => 'last_name']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.field-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'last_name']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b)): ?>
<?php $attributes = $__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b; ?>
<?php unset($__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b)): ?>
<?php $component = $__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b; ?>
<?php unset($__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b); ?>
<?php endif; ?>
                        </div>
                    </div>

                    
                    <div>
                        <label for="email" class="dashy-label">Work email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="<?php echo e(old('email')); ?>"
                            required
                            autocomplete="email"
                            placeholder="you@company.com"
                            class="dashy-input"
                        />
                        <?php if (isset($component)) { $__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.field-error','data' => ['name' => 'email']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.field-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'email']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b)): ?>
<?php $attributes = $__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b; ?>
<?php unset($__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b)): ?>
<?php $component = $__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b; ?>
<?php unset($__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b); ?>
<?php endif; ?>
                    </div>

                    
                    <div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="password" class="dashy-label">Password</label>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="••••••••"
                                    class="dashy-input"
                                    x-model="pw"
                                />
                                <?php if (isset($component)) { $__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.field-error','data' => ['name' => 'password']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.field-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'password']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b)): ?>
<?php $attributes = $__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b; ?>
<?php unset($__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b)): ?>
<?php $component = $__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b; ?>
<?php unset($__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b); ?>
<?php endif; ?>
                            </div>
                            <div>
                                <label for="password_confirmation" class="dashy-label">Repeat</label>
                                <input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="••••••••"
                                    class="dashy-input"
                                />
                            </div>
                        </div>

                        <div class="dashy-strength" aria-hidden="true">
                            <span :class="{ 'is-on': strength() >= 1 }"></span>
                            <span :class="{ 'is-on': strength() >= 2 }"></span>
                            <span :class="{ 'is-on': strength() >= 3 }"></span>
                            <span :class="{ 'is-on': strength() >= 4 }"></span>
                        </div>
                        <p class="dashy-help">8+ chars, mix case, number, symbol</p>
                    </div>

                    
                    <div class="pt-1">
                        <label class="flex items-start gap-2.5 text-sm select-none cursor-pointer">
                            <span class="dashy-checkbox mt-0.5">
                                <input type="checkbox" name="terms" value="1" <?php echo e(old('terms') ? 'checked' : ''); ?> required />
                                <svg viewBox="0 0 14 14" fill="none" class="dashy-check size-3">
                                    <path d="M2.25 7.25 5.25 10.25 11.75 3.75" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="text-[var(--ink)] leading-snug">
                                I agree to the
                                <a href="#" class="text-[var(--blue)] hover:text-[var(--blue-soft)] underline underline-offset-[4px] decoration-[1.5px]">Terms</a>
                                and
                                <a href="#" class="text-[var(--blue)] hover:text-[var(--blue-soft)] underline underline-offset-[4px] decoration-[1.5px]">Privacy Policy</a>.
                            </span>
                        </label>
                        <?php if (isset($component)) { $__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashy.field-error','data' => ['name' => 'terms']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashy.field-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'terms']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b)): ?>
<?php $attributes = $__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b; ?>
<?php unset($__attributesOriginal5c3c0a0474eb69b9828af65219c0fb8b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b)): ?>
<?php $component = $__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b; ?>
<?php unset($__componentOriginal5c3c0a0474eb69b9828af65219c0fb8b); ?>
<?php endif; ?>
                    </div>

                    <button type="submit" class="dashy-btn-primary group" data-test="register-user-button">
                        <span>Create workspace</span>
                        <svg viewBox="0 0 16 16" fill="none" class="size-[14px] transition-transform duration-200 group-hover:translate-x-0.5">
                            <path d="M3 8h10m-4-4 4 4-4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div class="dashy-divider pt-1">
                        <span>OR</span>
                    </div>

                    <a href="<?php echo e(route('auth.google.redirect')); ?>" class="dashy-btn-secondary" aria-label="<?php echo e(__('Continue with Google')); ?>" data-test="google-register">
                        <svg viewBox="0 0 24 24" class="size-[18px]" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        <span>Google</span>
                    </a>
                </form>
            </div>
        </section>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal694aac3c67d98e1abac25e51c700a4e9)): ?>
<?php $attributes = $__attributesOriginal694aac3c67d98e1abac25e51c700a4e9; ?>
<?php unset($__attributesOriginal694aac3c67d98e1abac25e51c700a4e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal694aac3c67d98e1abac25e51c700a4e9)): ?>
<?php $component = $__componentOriginal694aac3c67d98e1abac25e51c700a4e9; ?>
<?php unset($__componentOriginal694aac3c67d98e1abac25e51c700a4e9); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/pages/auth/register.blade.php ENDPATH**/ ?>