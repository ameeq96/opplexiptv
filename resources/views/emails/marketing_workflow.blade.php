<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $mailSubject }}</title>
</head>
<body style="margin:0;background:#f5f7fb;color:#0b1736;font-family:Arial,sans-serif;">
    <div style="max-width:620px;margin:0 auto;padding:32px 18px;">
        <div style="background:#fff;border:1px solid #dfe5ef;padding:28px;">
            <img src="{{ asset('images/opplexiptvlogo.webp') }}" alt="Opplex IPTV" width="150" style="display:block;margin-bottom:24px;">
            <p style="font-size:16px;line-height:1.65;margin:0 0 24px;">{{ $bodyText }}</p>
            <p style="margin:0 0 28px;">
                <a href="{{ $ctaUrl }}" style="display:inline-block;background:#ec1c24;color:#fff;text-decoration:none;padding:13px 20px;font-weight:700;">
                    {{ $ctaText }}
                </a>
            </p>
            <p style="font-size:12px;line-height:1.5;color:#5d6779;margin:0;border-top:1px solid #e7ebf1;padding-top:18px;">
                {{ __('marketing.email.permission') }}
                <a href="{{ $unsubscribeUrl }}" style="color:#334eaa;">{{ __('marketing.email.unsubscribe') }}</a>
            </p>
        </div>
    </div>
</body>
</html>
