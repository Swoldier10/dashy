<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="dashy-body min-h-dvh antialiased">
    <div class="relative min-h-dvh overflow-hidden flex flex-col">

        {{-- Decorative giant 'd' --}}
        <div aria-hidden="true" class="dashy-mark">d</div>

        {{-- Header --}}
        <header class="relative z-10 flex items-center justify-between px-7 sm:px-10 lg:px-14 py-7">
            <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2.5 group">
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
                {{ $headerNav ?? '' }}
            </nav>
        </header>

        {{-- Slot --}}
        <main class="relative z-10 flex-1 mx-auto w-full max-w-[1440px] px-7 sm:px-10 lg:px-14">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer class="relative z-10 px-7 sm:px-10 lg:px-14 py-7 mt-12">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs text-[var(--ink-dim)]">
                <div>© {{ date('Y') }} Dashy Labs · San Francisco</div>
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

    @persist('toast')
        <x-dashy.toaster />
    @endpersist
</body>
</html>
