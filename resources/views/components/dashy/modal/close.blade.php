{{-- <x-dashy.modal.close>…</x-dashy.modal.close>
     Wraps a clickable so activating it closes the nearest dashy-modal /
     dashy-drawer. Walks up the DOM looking for [data-modal-name]. --}}
@props([
    'name' => null,
])

<span
    x-data
    @click="
        const target = @js($name) ?? $el.closest('[data-modal-name]')?.dataset.modalName;
        if (target) $store.modals.close(target);
    "
    {{ $attributes }}
>
    {{ $slot }}
</span>
