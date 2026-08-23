<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ar', 'ur'], true) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('interface.not_found.title') }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
    
</head>
<body class="error-404-page">
    <div class="error-container">
        <div class="error-code">404</div>
        <div class="error-message">{{ __('interface.not_found.message') }}</div>
        <a href="{{ route('home') }}" class="home-button">{{ __('interface.not_found.home_button') }}</a>

        <div class="links">
            <p>{{ __('interface.not_found.try_links') }}</p>
            <a href="{{ route('home') }}">{{ __('interface.not_found.home') }}</a>
            <a href="{{ route('about') }}">{{ __('interface.not_found.about') }}</a>
            <a href="{{ route('contact') }}">{{ __('interface.not_found.contact') }}</a>
            <a href="{{ route('faqs') }}">{{ __('interface.not_found.faqs') }}</a>
            <a href="{{ route('pricing') }}">{{ __('interface.not_found.pricing') }}</a>
        </div>
    </div>
</body>
</html>

