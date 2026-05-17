<?php

namespace App\Support\Concerns;

/**
 * Bridges Livewire components and the dashy-ui Alpine layer.
 *
 * Each protected method dispatches a browser event that the Alpine stores
 * in resources/js/dashy-ui.js listen for. Keeps PHP code free of Alpine
 * details and gives every Livewire component a uniform UI surface.
 *
 * @mixin \Livewire\Component
 */
trait DispatchesDashyUi
{
    /**
     * Open a named modal/drawer.
     */
    protected function openModal(string $name): void
    {
        $this->dispatch('dashy-modal:open', name: $name);
    }

    /**
     * Close a named modal/drawer.
     */
    protected function closeModal(string $name): void
    {
        $this->dispatch('dashy-modal:close', name: $name);
    }

    /**
     * Close every open modal/drawer in the stack.
     */
    protected function closeAllModals(): void
    {
        $this->dispatch('dashy-modal:close-all');
    }

    /**
     * Push a transient toast.
     *
     * Variant must be one of: success, danger, warning, info.
     */
    protected function toast(string $variant, string $text, ?int $ttl = null): void
    {
        $this->dispatch('dashy-toast', variant: $variant, text: $text, ttl: $ttl ?? 4500);
    }
}
