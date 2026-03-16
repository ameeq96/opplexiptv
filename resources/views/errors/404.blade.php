<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>404 - Page Not Found</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
    
</head>
<body class="error-404-page">
    <div class="error-container">
        <div class="error-code">404</div>
        <div class="error-message">Oops! The page you are looking for doesn't exist.</div>
        <a href="{{ url('/') }}" class="home-button">Go to Homepage</a>

        <div class="links">
            <p>Or try one of these:</p>
            <a href="{{ url('/') }}">Home</a>
            <a href="{{ url('/about') }}">About Us</a>
            <a href="{{ url('/contact') }}">Contact</a>
            <a href="{{ url('/faqs') }}">FAQs</a>
            <a href="{{ url('/pricing') }}">Pricing</a>
        </div>
    </div>
</body>
</html>


