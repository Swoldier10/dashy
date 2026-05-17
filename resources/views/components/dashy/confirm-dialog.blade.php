@props([
    'name',
    'title',
    'description' => null,
    'confirmLabel' => 'Confirm',
    'confirmVariant' => 'danger',
    'cancelLabel' => 'Cancel',
    'wireClickConfirm' => null,  // e.g. "deleteProject"
    'dataTestConfirm' => null,
    'dataTestCancel' => null,
])

@php
    $modalAttributes = $attributes->whereDoesntStartWith(['wire:close']);
    $wireClose = $attributes->get('wire:close');
@endphp

<x-dashy.modal
    :name="$name"
    size="sm"
    wire:close="{{ $wireClose }}"
    {{ $modalAttributes }}
>
    <div class="space-y-4">
        <x-dashy.heading size="lg">{{ $title }}</x-dashy.heading>
        @if ($description)
            <x-dashy.subheading>{{ $description }}</x-dashy.subheading>
        @endif
        <div class="flex justify-end gap-2 pt-2">
            <x-dashy.modal.close>
                <x-dashy.button
                    type="button"
                    variant="filled"
                    @if ($wireClose) wire:click="{{ $wireClose }}" @endif
                    @if ($dataTestCancel) data-test="{{ $dataTestCancel }}" @endif
                >
                    {{ $cancelLabel }}
                </x-dashy.button>
            </x-dashy.modal.close>
            <x-dashy.button
                type="button"
                :variant="$confirmVariant"
                @if ($wireClickConfirm) wire:click="{{ $wireClickConfirm }}" @endif
                @if ($dataTestConfirm) data-test="{{ $dataTestConfirm }}" @endif
            >
                {{ $confirmLabel }}
            </x-dashy.button>
        </div>
    </div>
</x-dashy.modal>
