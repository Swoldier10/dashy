<div class="flex min-h-0 flex-1 flex-col" data-test="calendar-page">
    @include('livewire.calendar.partials.toolbar')

    <div class="flex min-h-0 flex-1">
        <main class="min-w-0 flex-1">
            @include('livewire.calendar.partials.fullcalendar')
        </main>

        <aside class="hidden w-[300px] shrink-0 border-l border-[var(--border)] bg-[var(--bg)] lg:block">
            @include('livewire.calendar.partials.sidebar')
        </aside>
    </div>

    @include('livewire.calendar.partials.event-drawer')

    <livewire:tasks.task-detail-drawer />
</div>
