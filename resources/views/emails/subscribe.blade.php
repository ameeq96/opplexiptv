<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ar', 'ur'], true) ? 'rtl' : 'ltr' }}">
<head>
    <title>{{ __('interface.email.subscribe.page_title') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #f4f4f4;
            padding-bottom: 10px;
        }
        p {
            margin: 10px 0;
        }
        .details {
            margin-top: 20px;
        }
        .details p {
            margin: 5px 0;
        }
        .details strong {
            display: inline-block;
            width: 100px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ __('interface.email.subscribe.heading') }}</h1>
        <p>{{ __('interface.email.common.greeting') }}</p>
        <p>{{ __('interface.email.subscribe.intro') }}</p>
        <div class="details">
            <p><strong>{{ __('interface.email.common.email') }}:</strong> {{ $details['email'] }}</p>
        </div>
        <p>{{ __('interface.email.common.regards') }}</p>
        <p>OPPLEX IPTV</p>
    </div>
</body>
</html>
