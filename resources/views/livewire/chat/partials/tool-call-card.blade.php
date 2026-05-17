@php
    /**
     * @var \App\Domains\Chat\Models\Message $message
     * @var array<string, mixed>|null $card
     */
    $name = $card['name'] ?? null;
    $mode = $card['mode'] ?? null;
@endphp

@if ($card === null)
@elseif ($mode === 'auto_read')
    @include('livewire.chat.partials.tool-cards.read-pill', ['message' => $message, 'card' => $card])
@elseif ($mode === 'compact_write')
    @include('livewire.chat.partials.tool-cards.compact-write', ['message' => $message, 'card' => $card])
@elseif ($mode === 'bulk_write')
    @include('livewire.chat.partials.tool-cards.bulk-write', ['message' => $message, 'card' => $card])
@elseif ($mode === 'structural_auto')
    @include('livewire.chat.partials.tool-cards.plan', ['message' => $message, 'card' => $card])
@elseif ($name === 'create_task')
    @include('livewire.chat.partials.tool-cards.create-task', ['message' => $message, 'card' => $card])
@elseif ($name === 'create_event')
    @include('livewire.chat.partials.tool-cards.create-event', ['message' => $message, 'card' => $card])
@elseif ($name === 'create_project')
    @include('livewire.chat.partials.tool-cards.create-project', ['message' => $message, 'card' => $card])
@elseif ($name === 'ask_user_choice')
    @include('livewire.chat.partials.tool-cards.ask-user-choice', ['message' => $message, 'card' => $card])
@endif
