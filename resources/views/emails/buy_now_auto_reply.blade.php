<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>{{ __('messages.email_title') }}</title>
</head>

<body>
    <p>{{ __('messages.dear', ['name' => $details['username']]) }}</p>

    <p>{{ __('messages.thank_you') }}</p>

    <p><strong>{{ __('messages.package_details') }}</strong></p>
    <ul>
        <li>{{ __('messages.package') }}: {{ str_replace('_', ' ', explode('_USD_', $details['package'])[0]) }}
            (${{ explode('_USD_', $details['package'])[1] }})</li>
        <li>{{ __('messages.email') }}: {{ $details['email'] }}</li>
        <li>{{ __('messages.phone') }}: {{ $details['phone'] }}</li>
        <li>{{ __('messages.message') }}: {{ $details['message'] }}</li>
    </ul>

    @php
        $packageName = str_replace('_', ' ', explode('_USD_', $details['package'])[0]);
        $packagePrice = '$' . explode('_USD_', $details['package'])[1];

        $waMessages = [
            'en' => "Hello, I am interested in the {$packageName} ({$packagePrice}) package.",
            'fr' => "Bonjour, je suis intéressé par le forfait {$packageName} ({$packagePrice}).",
            'it' => "Ciao, sono interessato al pacchetto {$packageName} ({$packagePrice}).",
        ];

        $lang = app()->getLocale();
        $waText = $waMessages[$lang] ?? $waMessages['en'];
    @endphp

    <p>{{ __('messages.contact_whatsapp') }}
        <a href="https://wa.me/16393903194?text={{ urlencode($waText) }}">
            +1 639-390-3194
        </a>
    </p>

    <br>
    <p>{{ __('messages.regards') }}<br>
        Opplex IPTV<br>
        {{ __('messages.tagline') }}</p>
</body>

</html>
