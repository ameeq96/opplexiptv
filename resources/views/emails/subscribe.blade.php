<!DOCTYPE html>
<html>
<head>
    <title>Contact Form Submission</title>
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
        <h1>Subscription Confirmation</h1>
        <p>Hi there,</p>
        <p>Thank you for subscribing to our newsletter. Here is the information we have received:</p>
        <div class="details">
            <p><strong>Email:</strong> {{ $details['email'] }}</p>
        </div>
        <p>Best regards,</p>
        <p>OPPLEX IPTV</p>
    </div>
</body>
</html>
