@php
    $brand = \App\Support\Theme::colors()['brand'];
    $siteName = setting('site_name', config('app.name'));
    $logo = setting('email_logo') ?: setting('logo');
    $logoUrl = $logo ? (str_starts_with((string) media_url($logo), '/') ? url(media_url($logo)) : media_url($logo)) : null;
    $logoWidth = (int) setting('email_logo_width', 160);
@endphp
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $siteName }}</title>
    <style>
        body { margin: 0; padding: 0; background: #f1f5f9; }
        .content p { margin: 0 0 16px; }
        .content h1, .content h2, .content h3 { margin: 0 0 12px; color: #0f172a; line-height: 1.3; }
        .content ul, .content ol { margin: 0 0 16px; padding-left: 20px; }
        .content li { margin-bottom: 4px; }
        .content blockquote { margin: 0 0 16px; padding: 10px 14px; border-left: 3px solid {{ $brand }}; background: #f8fafc; color: #334155; }
        .content a { color: {{ $brand }}; }
        .content a.button { color: #ffffff !important; background: {{ $brand }}; }
        .button-cell { background: {{ $brand }}; }
        @media (max-width: 620px) { .wrapper { padding: 12px !important; } .card { padding: 24px 20px !important; } }
    </style>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;">
    @if ($preheader ?? '')<div style="display:none;max-height:0;overflow:hidden;">{{ $preheader }}</div>@endif
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;">
        <tr><td class="wrapper" align="center" style="padding:32px 16px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;">
                {{-- Header --}}
                <tr><td align="{{ setting('email_logo_align', 'left') === 'center' ? 'center' : 'left' }}" style="padding:0 4px 20px;">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $siteName }}" width="{{ $logoWidth }}" style="display:block;width:{{ $logoWidth }}px;max-width:100%;height:auto;border:0;">
                    @else
                        <span style="font-family:Arial,Helvetica,sans-serif;font-size:24px;font-weight:800;letter-spacing:-0.5px;color:{{ $brand }};">{{ $siteName }}</span>
                    @endif
                </td></tr>
                {{-- Body --}}
                <tr><td class="card content" style="background:#ffffff;border-radius:16px;border-top:4px solid {{ $brand }};padding:32px;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.6;color:#334155;">
                    {!! $html !!}
                </td></tr>
                {{-- Footer --}}
                <tr><td style="padding:20px 4px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#64748b;" align="center">
                    @if (setting('email_footer'))
                        {!! nl2br(e(setting('email_footer'))) !!}<br>
                    @endif
                    {{ $siteName }}@if (setting('contact_phone_ca')) · {{ setting('contact_phone_ca') }}@endif @if (setting('contact_email')) · {{ setting('contact_email') }}@endif
                    @if (setting('contact_address'))<br>{{ str_replace("\n", ', ', setting('contact_address')) }}@endif
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
