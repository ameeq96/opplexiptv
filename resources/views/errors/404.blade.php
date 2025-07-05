<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>404 - Page Not Found</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8f9fa, #e0e0e0);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: #333;
        }

        .error-container {
            text-align: center;
            max-width: 600px;
            padding: 40px;
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .error-code {
            font-size: 100px;
            font-weight: bold;
            color: #007bff;
        }

        .error-message {
            font-size: 22px;
            margin: 10px 0 20px;
        }

        .home-button {
            display: inline-block;
            padding: 12px 30px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 30px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .home-button:hover {
            background-color: #0056b3;
        }

        .links {
            margin-top: 30px;
        }

        .links a {
            color: #007bff;
            margin: 0 10px;
            text-decoration: none;
            font-weight: 500;
        }

        .links a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .error-code {
                font-size: 70px;
            }

            .error-message {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
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
