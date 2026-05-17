<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="dashy-body min-h-screen">
        <div class="flex min-h-screen flex-col lg:flex-row">
            <livewire:app-sidebar />
            <main class="flex min-w-0 flex-1 flex-col pb-24 md:pb-20">
                {{ $slot }}
            </main>
        </div>

        @persist('toast')
            <x-dashy.toaster />
        @endpersist

        {{-- Global Connect-Codex modal so any page can open it via the
             open-connect-codex event. --}}
        <livewire:codex.connect-codex-modal />

        {{-- Global settings modal opened from the sidebar user-card and the
             bottom-nav Settings tab. --}}
        <livewire:settings-modal />

        <livewire:time-tracking.running-timer-pill />

        <x-dashy.bottom-tab-bar />
    </body>
</html>
