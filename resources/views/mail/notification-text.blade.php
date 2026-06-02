{{ __('Hi :name,', ['name' => $recipientName]) }}

{!! $headline !!}
@if ($body)

{!! $body !!}
@endif
@if ($ctaUrl)

{!! $ctaLabel !!}:
{!! $ctaUrl !!}
@endif

{{ __('You receive this e-mail because of your notification settings in Dashy. You can change them anytime under Settings → Notifications.') }}

— Dashy
