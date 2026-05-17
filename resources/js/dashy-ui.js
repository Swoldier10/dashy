/**
 * Dashy UI primitives — Alpine stores + window-event bridges that back the
 * <x-dashy-modal>, <x-dashy-drawer>, <x-dashy-popover>, <x-dashy-menu>,
 * <x-dashy-toaster> and friends. Loaded once per page via app.js.
 *
 * The PHP side talks to this layer through Livewire-dispatched browser
 * events (see app/Support/Concerns/DispatchesDashyUi.php):
 *   - 'dashy-modal:open'   { name }
 *   - 'dashy-modal:close'  { name }
 *   - 'dashy-toast'        { variant, text, ttl? }
 */

document.addEventListener('alpine:init', () => {
    /* -------------------------------------------------------------------
       Modal/drawer store. Single source of truth for "which named overlays
       are open". A stack so multiple modals/drawers can layer (parity with
       Flux's <dialog> behaviour).
       ------------------------------------------------------------------- */
    window.Alpine.store('modals', {
        stack: [],

        open(name) {
            if (! name || this.is(name)) return;
            this.stack.push(name);
            this._syncBodyLock();
        },

        close(name) {
            if (! name) return;
            const idx = this.stack.lastIndexOf(name);
            if (idx === -1) return;
            this.stack.splice(idx, 1);
            this._syncBodyLock();
        },

        closeAll() {
            this.stack = [];
            this._syncBodyLock();
        },

        is(name) {
            return this.stack.includes(name);
        },

        top() {
            return this.stack[this.stack.length - 1] ?? null;
        },

        _syncBodyLock() {
            document.body.classList.toggle('dashy-scroll-locked', this.stack.length > 0);
        },
    });

    /* -------------------------------------------------------------------
       Toaster store. Items auto-expire after `ttl` ms (default 4500).
       ------------------------------------------------------------------- */
    window.Alpine.store('toaster', {
        items: [],
        _seq: 0,

        push({ variant = 'info', text = '', ttl = 4500 } = {}) {
            if (! text) return;
            const id = ++this._seq;
            this.items.push({ id, variant, text });
            if (ttl > 0) {
                setTimeout(() => this.dismiss(id), ttl);
            }
            return id;
        },

        dismiss(id) {
            const idx = this.items.findIndex((t) => t.id === id);
            if (idx !== -1) this.items.splice(idx, 1);
        },

        clear() {
            this.items = [];
        },
    });
});

/* -----------------------------------------------------------------------
   Window-event bridges. PHP / Livewire dispatch these; Alpine stores
   react. Keep PHP code free of Alpine internals.
   ----------------------------------------------------------------------- */
function getStore(name) {
    return window.Alpine?.store?.(name);
}

window.addEventListener('dashy-modal:open', (e) => {
    const name = e.detail?.name ?? e.detail?.[0]?.name;
    if (name) getStore('modals')?.open(name);
});

window.addEventListener('dashy-modal:close', (e) => {
    const name = e.detail?.name ?? e.detail?.[0]?.name;
    if (name) getStore('modals')?.close(name);
});

window.addEventListener('dashy-toast', (e) => {
    const detail = e.detail ?? {};
    const payload = Array.isArray(detail) ? detail[0] : detail;
    if (! payload) return;
    getStore('toaster')?.push({
        variant: payload.variant ?? 'info',
        text: payload.text ?? '',
        ttl: payload.ttl ?? 4500,
    });
});

/* -----------------------------------------------------------------------
   Focus-trap helper (modal/drawer use). Remembers the previously focused
   element, traps Tab inside `el`, restores focus on release().
   Returns a release() callback.
   ----------------------------------------------------------------------- */
window.dashyTrapFocus = function (el) {
    if (! el) return () => {};
    const previously = document.activeElement instanceof HTMLElement ? document.activeElement : null;

    const focusable = () =>
        Array.from(
            el.querySelectorAll(
                'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
            )
        ).filter((n) => ! n.hasAttribute('inert') && n.offsetParent !== null);

    const onKeydown = (event) => {
        if (event.key !== 'Tab') return;
        const items = focusable();
        if (items.length === 0) {
            event.preventDefault();
            return;
        }
        const first = items[0];
        const last = items[items.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (! event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    };

    el.addEventListener('keydown', onKeydown);

    // Move focus into the dialog on first tick.
    requestAnimationFrame(() => {
        const items = focusable();
        if (items.length) items[0].focus();
        else el.focus({ preventScroll: true });
    });

    return () => {
        el.removeEventListener('keydown', onKeydown);
        if (previously && document.contains(previously)) {
            previously.focus({ preventScroll: true });
        }
    };
};


/* -----------------------------------------------------------------------
   Date picker — Alpine factory used by <x-dashy.date-picker>.
   - Two-way binds to a Livewire model name (works through wire:navigate
     remounts via $watch on $wire.<prop>).
   - Stores the value as ISO YYYY-MM-DD; renders a localised long form.
   - Keyboard nav (←/→ days, ↑/↓ weeks, PgUp/PgDn months, Enter selects,
     Escape closes).
   ----------------------------------------------------------------------- */
const MONTH_NAMES = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December',
];
const DAYS_SHORT = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

function pad(n) { return String(n).padStart(2, '0'); }
function fmtIso(date) { return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()); }
function fmtIsoDateTime(date) {
    return fmtIso(date) + 'T' + pad(date.getHours()) + ':' + pad(date.getMinutes());
}
function parseIso(s) {
    if (! s || typeof s !== 'string') return null;
    const m = s.match(/^(\d{4})-(\d{2})-(\d{2})(?:[T ](\d{2}):(\d{2}))?/);
    if (! m) return null;
    return new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3]), Number(m[4] ?? 0), Number(m[5] ?? 0));
}
function parseIsoHasTime(s) {
    return typeof s === 'string' && /^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}/.test(s);
}

window.dashyDatePicker = function (opts = {}) {
    return {
        MONTH_NAMES,
        DAYS_SHORT,

        modelName: opts.modelName || null,
        minDate: opts.minDate ? parseIso(opts.minDate) : null,
        maxDate: opts.maxDate ? parseIso(opts.maxDate) : null,
        onChange: opts.onChange || null,
        placeholder: opts.placeholder || '',
        withTime: opts.withTime === true,
        minuteStep: Number.isFinite(opts.minuteStep) ? opts.minuteStep : 5,

        open: false,
        value: '',          // ISO YYYY-MM-DD or YYYY-MM-DDTHH:mm when withTime
        display: '',        // localised long form
        focused: null,      // day number currently keyboard-focused
        month: 0,
        year: 0,
        hour: 0,            // 0–23, only used when withTime
        minute: 0,          // 0–59, only used when withTime
        noOfDays: [],
        blankdays: [],

        init() {
            const initial = this.modelName && this.$wire ? this.$wire.get(this.modelName) : null;
            const parsedInitial = parseIso(initial);
            const seed = parsedInitial ?? new Date();
            this.value = parsedInitial ? initial : '';
            this.month = seed.getMonth();
            this.year = seed.getFullYear();
            if (this.withTime) {
                this.hour = parsedInitial ? parsedInitial.getHours() : 0;
                this.minute = parsedInitial ? parsedInitial.getMinutes() : 0;
            }
            this.refreshDisplay();
            this.recomputeGrid();

            if (this.modelName && this.$wire?.$watch) {
                this.$wire.$watch(this.modelName, (incoming) => {
                    const next = (typeof incoming === 'string') ? incoming : '';
                    if (next === this.value) return;
                    this.value = next;
                    this.refreshDisplay();
                    const parsed = parseIso(next);
                    if (parsed) {
                        this.month = parsed.getMonth();
                        this.year = parsed.getFullYear();
                        if (this.withTime) {
                            this.hour = parsed.getHours();
                            this.minute = parsed.getMinutes();
                        }
                        this.recomputeGrid();
                    }
                });
            }
        },

        toggle() {
            this.open = ! this.open;
            if (this.open) {
                const parsed = parseIso(this.value);
                if (parsed) {
                    this.month = parsed.getMonth();
                    this.year = parsed.getFullYear();
                    if (this.withTime) {
                        this.hour = parsed.getHours();
                        this.minute = parsed.getMinutes();
                    }
                    this.recomputeGrid();
                }
                this.focused = parsed ? parsed.getDate() : new Date().getDate();
            }
        },

        close() { this.open = false; },

        prevMonth() {
            if (this.month === 0) { this.month = 11; this.year--; } else { this.month--; }
            this.recomputeGrid();
        },

        nextMonth() {
            if (this.month === 11) { this.month = 0; this.year++; } else { this.month++; }
            this.recomputeGrid();
        },

        recomputeGrid() {
            const daysInMonth = new Date(this.year, this.month + 1, 0).getDate();
            const dayOfWeek = new Date(this.year, this.month, 1).getDay();
            this.blankdays = Array.from({ length: dayOfWeek }, (_, i) => i);
            this.noOfDays = Array.from({ length: daysInMonth }, (_, i) => i + 1);
        },

        isSelected(d) {
            return this.value === fmtIso(new Date(this.year, this.month, d));
        },

        isToday(d) {
            const t = new Date();
            return t.getFullYear() === this.year
                && t.getMonth() === this.month
                && t.getDate() === d;
        },

        isInRange(d) {
            const t = new Date(this.year, this.month, d).setHours(0, 0, 0, 0);
            if (this.minDate && t < this.minDate.setHours(0, 0, 0, 0)) return false;
            if (this.maxDate && t > this.maxDate.setHours(0, 0, 0, 0)) return false;
            return true;
        },

        select(d) {
            if (! this.isInRange(d)) return;
            const date = new Date(this.year, this.month, d,
                this.withTime ? this.hour : 0,
                this.withTime ? this.minute : 0);
            const iso = this.withTime ? fmtIsoDateTime(date) : fmtIso(date);
            this.value = iso;
            this.refreshDisplay();
            // Keep panel open in time mode so the user can fine-tune HH/MM after the date click.
            if (! this.withTime) this.open = false;
            this.focused = d;
            if (this.modelName && this.$wire?.set) this.$wire.set(this.modelName, iso);
            if (this.onChange && this.$wire) {
                const fn = this.onChange.replace(/\(\)$/, '');
                if (typeof this.$wire[fn] === 'function') this.$wire[fn]();
            }
        },

        clear() {
            this.value = '';
            this.display = '';
            this.hour = 0;
            this.minute = 0;
            this.open = false;
            if (this.modelName && this.$wire?.set) this.$wire.set(this.modelName, null);
            if (this.onChange && this.$wire) {
                const fn = this.onChange.replace(/\(\)$/, '');
                if (typeof this.$wire[fn] === 'function') this.$wire[fn]();
            }
        },

        onTimeInput() {
            if (! this.withTime) return;
            if (! Number.isFinite(this.hour) || ! Number.isFinite(this.minute)) return;
            this.hour = Math.min(23, Math.max(0, Math.trunc(this.hour)));
            this.minute = Math.min(59, Math.max(0, Math.trunc(this.minute)));
            // Only emit a new value if a date has been picked. Otherwise just keep internal state.
            const parsed = parseIso(this.value);
            if (! parsed) return;
            const date = new Date(parsed.getFullYear(), parsed.getMonth(), parsed.getDate(), this.hour, this.minute);
            const iso = fmtIsoDateTime(date);
            if (iso === this.value) return;
            this.value = iso;
            this.refreshDisplay();
            if (this.modelName && this.$wire?.set) this.$wire.set(this.modelName, iso);
            if (this.onChange && this.$wire) {
                const fn = this.onChange.replace(/\(\)$/, '');
                if (typeof this.$wire[fn] === 'function') this.$wire[fn]();
            }
        },

        clampTime() {
            if (! this.withTime) return;
            if (! Number.isFinite(this.hour)) this.hour = 0;
            if (! Number.isFinite(this.minute)) this.minute = 0;
            this.onTimeInput();
        },

        refreshDisplay() {
            const parsed = parseIso(this.value);
            if (! parsed) { this.display = ''; return; }
            this.display = (this.withTime && parseIsoHasTime(this.value))
                ? parsed.toLocaleString(undefined, {
                    year: 'numeric', month: '2-digit', day: '2-digit',
                    hour: '2-digit', minute: '2-digit', hour12: false,
                })
                : parsed.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
        },

        keyDown(ev) {
            if (! this.open) return;
            if (ev.target && ev.target.classList && ev.target.classList.contains('dashy-date-time-input')) {
                if (ev.key === 'Escape') { ev.preventDefault(); this.close(); }
                return;
            }
            const max = this.noOfDays.length;
            const cur = this.focused ?? 1;
            if (ev.key === 'ArrowLeft')  { ev.preventDefault(); this.focused = Math.max(1, cur - 1); }
            else if (ev.key === 'ArrowRight') { ev.preventDefault(); this.focused = Math.min(max, cur + 1); }
            else if (ev.key === 'ArrowUp')    { ev.preventDefault(); this.focused = Math.max(1, cur - 7); }
            else if (ev.key === 'ArrowDown')  { ev.preventDefault(); this.focused = Math.min(max, cur + 7); }
            else if (ev.key === 'PageUp')     { ev.preventDefault(); this.prevMonth(); }
            else if (ev.key === 'PageDown')   { ev.preventDefault(); this.nextMonth(); }
            else if (ev.key === 'Enter')      { ev.preventDefault(); this.select(this.focused ?? 1); }
            else if (ev.key === 'Escape')     { ev.preventDefault(); this.close(); }
        },
    };
};


/* -----------------------------------------------------------------------
   Searchable select — Alpine factory used by <x-dashy.searchable-select>.
   - Two-way binds to a Livewire model name via $wire.get / $wire.set,
     with $wire.$watch to react to external changes (mirrors date-picker).
   - Options are a list of { value, label } strings (server-normalized).
   - When closed: shows the selected label or placeholder.
   - When open: a search input filters the list; ↑/↓ moves focus,
     Enter selects, Escape / outside-click closes.
   ----------------------------------------------------------------------- */
window.dashySearchableSelect = function (opts = {}) {
    return {
        modelName: opts.modelName || null,
        allOptions: Array.isArray(opts.options) ? opts.options : [],
        placeholder: opts.placeholder || '',
        searchPlaceholder: opts.searchPlaceholder || opts.placeholder || '',
        emptyMessage: opts.emptyMessage || '',

        open: false,
        search: '',
        focusedValue: null,
        value: null,

        get filtered() {
            if (! this.search) return this.allOptions;
            const q = this.search.toLowerCase();
            return this.allOptions.filter((o) => String(o.label).toLowerCase().includes(q));
        },

        get visibleCount() {
            return this.filtered.length;
        },

        get selectedLabel() {
            const found = this.allOptions.find((o) => String(o.value) === String(this.value));
            return found ? found.label : '';
        },

        init() {
            const initial = this.modelName && this.$wire ? this.$wire.get(this.modelName) : null;
            this.value = initial !== null && initial !== undefined && initial !== '' ? String(initial) : null;

            if (this.modelName && this.$wire?.$watch) {
                this.$wire.$watch(this.modelName, (incoming) => {
                    const next = incoming !== null && incoming !== undefined && incoming !== '' ? String(incoming) : null;
                    if (next === this.value) return;
                    this.value = next;
                });
            }
        },

        toggle() {
            if (this.open) this.close();
            else this.openPanel();
        },

        openPanel() {
            this.search = '';
            const match = this.allOptions.find((o) => String(o.value) === String(this.value));
            this.focusedValue = match ? String(match.value) : (this.allOptions[0] ? String(this.allOptions[0].value) : null);
            this.open = true;
            this.$nextTick(() => {
                this.$refs.search?.focus();
                this.scrollFocusedIntoView();
            });
        },

        close() {
            if (! this.open) return;
            this.open = false;
            this.search = '';
            this.focusedValue = null;
        },

        focusNext() {
            const list = this.filtered;
            if (list.length === 0) return;
            const idx = list.findIndex((o) => String(o.value) === String(this.focusedValue));
            const next = idx < 0 ? 0 : Math.min(idx + 1, list.length - 1);
            this.focusedValue = String(list[next].value);
            this.scrollFocusedIntoView();
        },

        focusPrev() {
            const list = this.filtered;
            if (list.length === 0) return;
            const idx = list.findIndex((o) => String(o.value) === String(this.focusedValue));
            const prev = idx < 0 ? 0 : Math.max(idx - 1, 0);
            this.focusedValue = String(list[prev].value);
            this.scrollFocusedIntoView();
        },

        focusOption(value) {
            this.focusedValue = value !== null && value !== undefined ? String(value) : null;
        },

        scrollFocusedIntoView() {
            this.$nextTick(() => {
                const list = this.$refs.listbox;
                if (! list || this.focusedValue === null) return;
                const node = list.querySelector(`[role="option"][data-value="${(window.CSS && window.CSS.escape) ? CSS.escape(String(this.focusedValue)) : String(this.focusedValue)}"]`);
                node?.scrollIntoView({ block: 'nearest' });
            });
        },

        selectFocused() {
            const list = this.filtered;
            if (list.length === 0) return;
            const opt = list.find((o) => String(o.value) === String(this.focusedValue)) || list[0];
            if (opt) this.select(opt);
        },

        selectByValue(value) {
            const opt = this.allOptions.find((o) => String(o.value) === String(value));
            if (opt) this.select(opt);
        },

        select(opt) {
            this.value = String(opt.value);
            if (this.modelName && this.$wire?.set) this.$wire.set(this.modelName, opt.value);
            this.close();
        },
    };
};
