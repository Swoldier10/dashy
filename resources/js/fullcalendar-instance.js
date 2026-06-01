import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import allLocales from '@fullcalendar/core/locales-all';

const VIEW_MAP = {
    week: 'timeGridWeek',
    day: 'timeGridDay',
    month: 'dayGridMonth',
};

document.addEventListener('alpine:init', () => {
    window.Alpine.data('dashyCalendar', (config = {}) => ({
        calendar: null,
        _mql: null,
        _onMqlChange: null,
        _livewireUnbinders: [],

        init() {
            this.calendar = new Calendar(this.$refs.cal, {
                plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
                initialView: VIEW_MAP[config.view] ?? 'timeGridWeek',
                initialDate: config.anchor || undefined,
                headerToolbar: false,
                firstDay: 1,
                nowIndicator: true,
                slotDuration: '00:15:00',
                slotLabelInterval: '01:00',
                snapDuration: '00:15:00',
                scrollTime: '08:00:00',
                slotLabelFormat: { hour: 'numeric', meridiem: 'short', hour12: true },
                dayHeaderContent: (arg) => ({
                    html: `<div class="dashy-day-header${arg.isToday ? ' is-today' : ''}">
                        <span class="dashy-day-header__dow">${arg.date
                            .toLocaleDateString(config.locale || 'en', { weekday: 'short' })
                            .toUpperCase()}</span>
                        <span class="dashy-day-header__num">${arg.date.getDate()}</span>
                    </div>`,
                }),
                editable: true,
                selectable: true,
                selectMirror: true,
                slotEventOverlap: false,
                height: '100%',
                expandRows: true,
                dayMaxEvents: true,
                locales: allLocales,
                locale: config.locale || 'en',
                events: (info, success, failure) => {
                    this.$wire.getCalendarPayload(info.startStr, info.endStr)
                        .then((payload) => success(payload ?? []))
                        .catch(failure);
                },
                eventDrop: (info) => {
                    const id = info.event.extendedProps.eventId;
                    if (!id) {
                        info.revert();
                        return;
                    }
                    this.$wire
                        .moveEvent(id, info.event.start.toISOString())
                        .catch(() => info.revert());
                },
                eventResize: (info) => {
                    const id = info.event.extendedProps.eventId;
                    if (!id || !info.event.end) {
                        info.revert();
                        return;
                    }
                    this.$wire
                        .resizeEvent(id, info.event.end.toISOString())
                        .catch(() => info.revert());
                },
                eventClick: (info) => {
                    const { type, eventId, taskId } = info.event.extendedProps;
                    if (type === 'task') {
                        if (taskId) this.$wire.openTaskDetail(taskId);
                    } else if (eventId) {
                        this.$wire.openEventDetail(eventId);
                    }
                },
                select: (info) => {
                    this.$wire.createEvent(info.startStr, info.endStr);
                    this.calendar.unselect();
                },
            });

            this.calendar.render();
            this._applyMobileView();

            this._livewireUnbinders.push(
                this.$wire.$watch('view', (v) => {
                    const fcView = VIEW_MAP[v];
                    if (fcView && this.calendar.view.type !== fcView) {
                        this.calendar.changeView(fcView);
                    }
                }),
            );
            this._livewireUnbinders.push(
                this.$wire.$watch('anchor', (d) => {
                    if (d) this.calendar.gotoDate(d);
                }),
            );

            const refetch = () => {
                if (! this.calendar) {
                    return;
                }
                this.calendar.refetchEvents();
            };
            // `Livewire.on` returns an unsubscribe function. These are GLOBAL
            // listeners, so they must be torn down in destroy() — otherwise after
            // a `wire:navigate` away from the calendar the closure still fires
            // against a now-null `this.calendar`, throwing inside Livewire's event
            // dispatch loop and aborting the DOM morph (e.g. blanking the task
            // drawer when a time entry dispatches `task-list-changed`).
            // See https://livewire.laravel.com/docs/4.x/javascript.
            this._livewireUnbinders.push(
                window.Livewire.on('calendar-events-changed', refetch),
                window.Livewire.on('task-list-changed', refetch),
            );

            this._mql = window.matchMedia('(max-width: 767px)');
            this._onMqlChange = () => this._applyMobileView();
            this._mql.addEventListener('change', this._onMqlChange);
        },

        _applyMobileView() {
            if (!this.calendar) return;
            const isMobile = window.matchMedia('(max-width: 767px)').matches;
            if (isMobile && this.calendar.view.type === 'timeGridWeek') {
                this.calendar.changeView('timeGridDay');
            }
        },

        destroy() {
            if (this._mql && this._onMqlChange) {
                this._mql.removeEventListener('change', this._onMqlChange);
            }
            this._livewireUnbinders.forEach((fn) => {
                try { fn?.(); } catch (_) { /* noop */ }
            });
            this.calendar?.destroy();
            this.calendar = null;
        },
    }));
});
