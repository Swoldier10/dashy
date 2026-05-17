<div
    class="h-full"
    wire:ignore
    x-data="dashyCalendar({
        view: @js($view),
        anchor: @js($anchor),
        locale: @js(app()->getLocale()),
    })"
    data-test="calendar-fullcalendar"
>
    <div x-ref="cal" class="h-full"></div>
</div>
