<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Two-Factor Authentication | Opplex IPTV</title>
    <style>
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; display: grid; place-items: center; padding: 20px; background: #07122e; color: #172033; font-family: Arial, sans-serif; }
        .two-factor-card { width: min(430px, 100%); padding: 30px; background: #fff; border-radius: 18px; box-shadow: 0 24px 64px rgba(0, 0, 0, .35); }
        h1 { margin: 0 0 8px; font-size: 25px; }
        p { margin: 0 0 20px; color: #64748b; line-height: 1.55; }
        label { display: block; margin-bottom: 7px; font-weight: 700; }
        input { width: 100%; padding: 13px 14px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 18px; letter-spacing: .12em; }
        input:focus { border-color: #2563eb; outline: 3px solid rgba(37, 99, 235, .15); }
        button { width: 100%; margin-top: 16px; padding: 13px 16px; border: 0; border-radius: 10px; background: #e21d2b; color: #fff; font-weight: 800; cursor: pointer; }
        .error { margin-top: 7px; color: #b91c1c; font-size: 13px; }
        .logout { margin-top: 14px; text-align: center; }
        .logout button { width: auto; margin: 0; padding: 0; color: #475569; background: transparent; font-weight: 600; }
    </style>
</head>
<body>
    <main class="two-factor-card">
        <h1>Two-factor authentication</h1>
        <p>Enter the 6-digit code from your authenticator app, or use one unused recovery code.</p>

        <form method="POST" action="{{ route('admin.two-factor.verify') }}">
            @csrf
            <label for="two-factor-code">Authentication code</label>
            <input id="two-factor-code" name="code" type="text" autocomplete="one-time-code" autocapitalize="characters"
                maxlength="20" required autofocus aria-describedby="code-error">
            @error('code')
                <div class="error" id="code-error">{{ $message }}</div>
            @enderror
            <button type="submit">Verify and continue</button>
        </form>

        <form class="logout" method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit">Sign in with another account</button>
        </form>
    </main>
</body>
</html>
