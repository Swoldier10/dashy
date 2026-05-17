@props([])

<div
    x-data="{
        focusItem(direction) {
            const items = this.items();
            if (! items.length) return;
            const current = items.indexOf(document.activeElement);
            let next = current;
            if (direction === 'next') next = current === -1 ? 0 : (current + 1) % items.length;
            else if (direction === 'prev') next = current === -1 ? items.length - 1 : (current - 1 + items.length) % items.length;
            else if (direction === 'first') next = 0;
            else if (direction === 'last') next = items.length - 1;
            items[next]?.focus();
        },
        items() {
            return Array.from(this.$el.querySelectorAll('[role=\'menuitem\']:not([disabled])'));
        },
    }"
    role="menu"
    @keydown.arrow-down.prevent="focusItem('next')"
    @keydown.arrow-up.prevent="focusItem('prev')"
    @keydown.home.prevent="focusItem('first')"
    @keydown.end.prevent="focusItem('last')"
    {{ $attributes->class(['dashy-menu']) }}
>
    {{ $slot }}
</div>
