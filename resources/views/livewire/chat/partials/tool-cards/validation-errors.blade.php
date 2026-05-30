{{-- Shared validation-error list for the AI confirm cards (create-task /
     create-event / create-project). Expects $validationErrors in scope. --}}
@if (($validationErrors ?? []) !== [])
    <ul class="list-disc space-y-1 rounded-md border px-4 py-2 pl-8 text-sm"
        style="border-color: var(--state-error); color: var(--state-error); background-color: color-mix(in srgb, var(--state-error) 6%, transparent);"
        data-test="tool-call-validation-errors"
    >
        @foreach ($validationErrors as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif
