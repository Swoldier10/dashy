@php
    $teamName = $team?->name ?? __('a team');
    $teamInitials = $team?->initials() ?? '·';
    $inviterName = $inviter?->name ?? __('A teammate');
    $inviterAvatar = $inviter?->avatar ?: null;

    $logoPath = public_path('dashy-logo.png');
    $logoCid = file_exists($logoPath) ? $message->embed($logoPath) : null;

    $teamLogoPath = $team?->logo ? public_path('storage/'.$team->logo) : null;
    $teamLogoCid = ($teamLogoPath && file_exists($teamLogoPath)) ? $message->embed($teamLogoPath) : null;

    $expiresLabel = $expiresAt ? $expiresAt->format('F j, Y') : null;
    $roleLabel = $role !== '' ? $role : __('Member');
    $isOwnerRole = mb_strtolower($roleLabel) === mb_strtolower(__('Owner'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title>{{ __("You're invited to join :team", ['team' => $teamName]) }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f5f3ef; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; color:#1a1a1a; -webkit-font-smoothing:antialiased;">

{{-- Preheader (hidden, but shown in inbox preview) --}}
<div style="display:none; max-height:0; overflow:hidden; mso-hide:all; font-size:1px; line-height:1px; color:#f5f3ef;">
    {{ __(':inviter invited you to join :team on Dashy as a :role.', ['inviter' => $inviterName, 'team' => $teamName, 'role' => $roleLabel]) }}
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

                {{-- Team identity badge --}}
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin:0 auto 24px;">
                <tr>
                <td align="center">
                    @if ($teamLogoCid)
                        <img src="{{ $teamLogoCid }}" alt="{{ $teamName }}" width="64" height="64" style="display:block; border-radius:14px; object-fit:cover; border:1px solid rgba(16,24,40,0.06);">
                    @else
                        <div style="width:64px; height:64px; border-radius:14px; background-color:#e9b8c9; color:#31241f; font-size:22px; font-weight:600; line-height:64px; text-align:center; letter-spacing:-0.01em;">{{ $teamInitials }}</div>
                    @endif
                </td>
                </tr>
                </table>

                {{-- Headline --}}
                <h1 style="margin:0 0 12px; font-family: Georgia, 'Times New Roman', serif; font-size:26px; line-height:1.25; font-weight:600; color:#1a1a1a; text-align:center; letter-spacing:-0.015em;">
                    {{ __('You\'re invited to join :team', ['team' => $teamName]) }}
                </h1>

                {{-- Inviter line --}}
                <p style="margin:0 0 22px; font-size:15px; line-height:1.55; color:#6b6b6b; text-align:center;">
                    {{ __(':inviter has invited you to collaborate on Dashy.', ['inviter' => $inviterName]) }}
                </p>

                {{-- Role pill --}}
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin:0 auto 28px;">
                <tr>
                <td align="center">
                    <span style="display:inline-block; padding:6px 14px; border-radius:999px; font-size:12px; font-weight:500; letter-spacing:0.02em; text-transform:uppercase; {{ $isOwnerRole ? 'background-color:#5992c6; color:#ffffff;' : 'background-color:#f0eee9; color:#6b6b6b;' }}">
                        {{ __('Joining as :role', ['role' => $roleLabel]) }}
                    </span>
                </td>
                </tr>
                </table>

                {{-- CTA button --}}
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin:0 auto 24px;">
                <tr>
                <td align="center" bgcolor="#5992c6" style="border-radius:10px;">
                    <a href="{{ $acceptUrl }}" target="_blank" style="display:inline-block; padding:14px 32px; font-size:15px; font-weight:600; letter-spacing:-0.005em; color:#ffffff; text-decoration:none; border-radius:10px; line-height:1;">
                        {{ __('Accept invitation') }}
                    </a>
                </td>
                </tr>
                </table>

                @if ($expiresLabel)
                    <p style="margin:0; font-size:13px; line-height:1.5; color:#9a9a9a; text-align:center;">
                        {{ __('This invitation expires on :date.', ['date' => $expiresLabel]) }}
                    </p>
                @endif

                {{-- Divider --}}
                <div style="margin:32px 0 24px; height:1px; background-color:rgba(16,24,40,0.06); line-height:1px; font-size:1px;">&nbsp;</div>

                {{-- Link fallback --}}
                <p style="margin:0 0 8px; font-size:12px; line-height:1.5; color:#9a9a9a;">
                    {{ __('Trouble with the button? Paste this link into your browser:') }}
                </p>
                <p style="margin:0; font-size:12px; line-height:1.5; word-break:break-all;">
                    <a href="{{ $acceptUrl }}" style="color:#5992c6; text-decoration:none;">{{ $acceptUrl }}</a>
                </p>
            </td>
        </tr>

        {{-- Footer --}}
        <tr>
            <td align="center" style="padding:24px 16px 8px;">
                <p style="margin:0; font-size:12px; line-height:1.55; color:#9a9a9a; max-width:420px;">
                    {{ __("If you weren't expecting this invitation, you can safely ignore this email — no account will be created.") }}
                </p>
            </td>
        </tr>

    </table>

</td>
</tr>
</table>

</body>
</html>
