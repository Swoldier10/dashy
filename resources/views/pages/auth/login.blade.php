<x-layouts::auth.dashy :title="__('Sign in')">
    <x-slot:headerNav>
        <a href="#" class="hidden sm:flex items-center gap-2.5 text-[var(--ink)] hover:text-[var(--blue-soft)] transition-colors">
            <span class="dashy-pulse"></span>
            <span class="underline underline-offset-[5px] decoration-[1.5px] decoration-[var(--ink-muted)]/60 hover:decoration-[var(--blue-soft)]">Status: All systems operational</span>
        </a>
        <a href="#" class="text-[var(--ink-muted)] hover:text-[var(--ink)] transition-colors">Help</a>
    </x-slot:headerNav>

    <div class="grid lg:grid-cols-12 items-start gap-12 lg:gap-20 pt-8 sm:pt-14 lg:pt-24 pb-20">

        {{-- LEFT — Hero --}}
        <section class="lg:col-span-6 dashy-fade" style="--delay: 0ms">
            <div class="dashy-eyebrow mb-7">
                <span class="size-1.5 rounded-full bg-[var(--blue)] dashy-pulse-blue"></span>
                Sign in to Dashy
            </div>

            <h1 class="dashy-headline">
                Less app-<wbr>switching.<br>
                <em>More finishing.</em>
            </h1>

            <p class="mt-7 max-w-md text-[15px] leading-relaxed text-[var(--ink-muted)]">
                Tasks, docs, goals and chat in one workspace your team will actually open. Sign in to keep going.
            </p>

            <div class="dashy-stats mt-12">
                <div><strong>40k+</strong> <span>teams</span></div>
                <div><strong>SOC 2</strong> <span>Type II</span></div>
                <div><strong>99.99%</strong> <span>uptime</span></div>
            </div>
        </section>

        {{-- RIGHT — Card --}}
        <section class="lg:col-span-6 lg:pt-2 dashy-fade" style="--delay: 220ms">
            <div class="dashy-card">
                <x-auth-session-status :status="session('status')" />

                <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="dashy-label">Work email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', request('email')) }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="you@company.com"
                            class="dashy-input"
                        />
                        <x-dashy.field-error name="email" />
                    </div>

                    <div>
                        <label for="password" class="dashy-label">Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="dashy-input"
                        />
                        <x-dashy.field-error name="password" />
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2.5 text-sm select-none cursor-pointer">
                            <span class="dashy-checkbox">
                                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} />
                                <svg viewBox="0 0 14 14" fill="none" class="dashy-check size-3">
                                    <path d="M2.25 7.25 5.25 10.25 11.75 3.75" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="text-[var(--ink)]">Remember me</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" wire:navigate class="text-sm text-[var(--blue)] hover:text-[var(--blue-soft)] underline underline-offset-[5px] decoration-[1.5px] transition-colors">
                                Forgot?
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="dashy-btn-primary group" data-test="login-button">
                        <span>Open workspace</span>
                        <svg viewBox="0 0 16 16" fill="none" class="size-[14px] transition-transform duration-200 group-hover:translate-x-0.5">
                            <path d="M3 8h10m-4-4 4 4-4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div class="dashy-divider pt-1">
                        <span>OR</span>
                    </div>

                    <a href="{{ route('auth.google.redirect') }}" class="dashy-btn-secondary" aria-label="{{ __('Continue with Google') }}" data-test="google-login">
                        <svg viewBox="0 0 24 24" class="size-[18px]" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        <span>Google</span>
                    </a>

                    @if (Route::has('register'))
                        <p class="text-center text-sm text-[var(--ink-muted)] pt-1">
                            New to Dashy?
                            <a href="{{ route('register') }}" wire:navigate class="dashy-header-link ml-1">Create account</a>
                        </p>
                    @endif
                </form>
            </div>
        </section>
    </div>
</x-layouts::auth.dashy>
