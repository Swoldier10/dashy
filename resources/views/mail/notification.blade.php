@php
    $logoPath = public_path('dashy-logo.png');
    $logoCid = (isset($message) && file_exists($logoPath)) ? $message->embed($logoPath) : null;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title>{{ $headline }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f5f3ef; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; color:#1a1a1a; -webkit-font-smoothing:antialiased;">

{{-- Preheader (hidden, but shown in inbox preview) --}}
<div style="display:none; max-height:0; overflow:hidden; mso-hide:all; font-size:1px; line-height:1px; color:#f5f3ef;">
    {{ $body ?? $headline }}
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f5f3ef;">
<tr>
<td align="center" style="padding:32px 16px;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px; margin:0 auto;">

        {{-- Header: Dashy wordmark --}}
        <tr>
            <td align="center" style="padding:8px 0 28px;">
                @if ($logoCid)
                    <img src="{{ $logoCid }}" alt="Dashy" width="110" style="display:block; height:auto; max-width:110px; border:0; outline:none; text-decoration:none;">
                @else
                    <div style="font-family: Georgia, 'Times New Roman', serif; font-size:24px; font-weight:600; letter-spacing:-0.01em; color:#1a1a1a;">Dashy</div>
                @endif
            </td>
        </tr>

        {{-- Main card --}}
        <tr>
            <td style="background-color:#ffffff; border-radius:14px; padding:40px 36px; box-shadow:0 1px 2px rgba(16,24,40,0.04), 0 1px 1px rgba(16,24,40,0.02);">

                <p style="margin:0 0 18px; font-size:15px; line-height:1.55; color:#6b6b6b;">
                    {{ __('Hi :name,', ['name' => $recipientName]) }}
                </p>

                {{-- Headline --}}
                <h1 style="margin:0 0 12px; font-family: Georgia, 'Times New Roman', serif; font-size:24px; line-height:1.3; font-weight:600; color:#1a1a1a; letter-spacing:-0.015em;">
                    {{ $headline }}
                </h1>

                @if ($body)
                    <p style="margin:0 0 26px; font-size:15px; line-height:1.55; color:#6b6b6b;">
                        {{ $body }}
                    </p>
                @endif

                @if ($ctaUrl)
                    {{-- CTA button --}}
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:6px 0 24px;">
                    <tr>
                    <td align="center" bgcolor="#5992c6" style="border-radius:10px;">
                        <a href="{{ $ctaUrl }}" target="_blank" style="display:inline-block; padding:14px 32px; font-size:15px; font-weight:600; letter-spacing:-0.005em; color:#ffffff; text-decoration:none; border-radius:10px; line-height:1;">
                            {{ $ctaLabel }}
                        </a>
                    </td>
                    </tr>
                    </table>

                    {{-- Divider --}}
                    <div style="margin:8px 0 24px; height:1px; background-color:rgba(16,24,40,0.06); line-height:1px; font-size:1px;">&nbsp;</div>

                    {{-- Link fallback --}}
                    <p style="margin:0 0 8px; font-size:12px; line-height:1.5; color:#9a9a9a;">
                        {{ __('Trouble with the button? Paste this link into your browser:') }}
                    </p>
                    <p style="margin:0; font-size:12px; line-height:1.5; word-break:break-all;">
                        <a href="{{ $ctaUrl }}" style="color:#5992c6; text-decoration:none;">{{ $ctaUrl }}</a>
                    </p>
                @endif
            </td>
        </tr>

        {{-- Footer --}}
        <tr>
            <td align="center" style="padding:24px 16px 8px;">
                <p style="margin:0; font-size:12px; line-height:1.55; color:#9a9a9a; max-width:420px;">
                    {{ __('You receive this e-mail because of your notification settings in Dashy. You can change them anytime under Settings → Notifications.') }}
                </p>
            </td>
        </tr>

    </table>

</td>
</tr>
</table>

</body>
</html>
