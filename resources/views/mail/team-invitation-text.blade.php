{{ __("You're invited to join :team on Dashy", ['team' => $team?->name ?? __('a team')]) }}

{{ __(':inviter has invited you to join :team as a :role.', [
    'inviter' => $inviter?->name ?? __('A teammate'),
    'team' => $team?->name ?? __('a team'),
    'role' => $role !== '' ? $role : __('Member'),
]) }}

{{ __('Accept the invitation:') }}
{{ $acceptUrl }}

@if ($expiresAt)
{{ __('This invitation expires on :date.', ['date' => $expiresAt->format('F j, Y')]) }}

@endif
{{ __("If you weren't expecting this invitation, you can safely ignore this email.") }}

— Dashy
