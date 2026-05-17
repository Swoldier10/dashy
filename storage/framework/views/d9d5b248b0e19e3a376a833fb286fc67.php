<div
    class="h-full"
    wire:ignore
    x-data="dashyCalendar({
        view: <?php echo \Illuminate\Support\Js::from($view)->toHtml() ?>,
        anchor: <?php echo \Illuminate\Support\Js::from($anchor)->toHtml() ?>,
        locale: <?php echo \Illuminate\Support\Js::from(app()->getLocale())->toHtml() ?>,
    })"
    data-test="calendar-fullcalendar"
>
    <div x-ref="cal" class="h-full"></div>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/calendar/partials/fullcalendar.blade.php ENDPATH**/ ?>