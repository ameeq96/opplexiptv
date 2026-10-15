<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Two-Factor Authentication | Opplex IPTV</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite('resources/css/admin.css')
</head>
<body class="admin-auth-body">
    <main class="admin-auth-shell" aria-labelledby="admin-two-factor-title">
        <aside class="admin-auth-aside">
            <img class="admin-auth-brand" src="{{ asset('images/opplexiptvlogo.webp') }}"
                alt="Opplex IPTV" width="250" height="75">
            <div>
                <p class="admin-auth-kicker">ACCOUNT SECURITY</p>
                <h2>One more check.<br>Just for you.</h2>
                <p class="admin-auth-note">An additional layer of protection for your account, your team and your customers.</p>
            </div>
            <p class="admin-auth-note mb-0">Never share your authentication or recovery codes.</p>
        </aside>
        <section class="admin-auth-main">
            <div class="admin-auth-card">
                <header class="mb-4">
                    <p class="admin-auth-kicker">VERIFY YOUR IDENTITY</p>
                    <h1 id="admin-two-factor-title">Two-factor authentication</h1>
                    <p class="text-muted mb-0">Enter the 6-digit code from your authenticator app, or use one unused recovery code.</p>
                </header>

                <form method="POST" action="{{ route('admin.two-factor.verify') }}">
                    @csrf
                    <div class="admin-auth-field mb-4">
                        <label class="form-label" for="two-factor-code">Authentication code</label>
                        <input class="form-control @error('code') is-invalid @enderror" id="two-factor-code" name="code" type="text" autocomplete="one-time-code" autocapitalize="characters"
                            maxlength="20" required autofocus aria-describedby="code-error">
                        @error('code')
                            <div class="invalid-feedback" id="code-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Verify and continue</button>
                </form>

                <form class="mt-3" method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="btn btn-link text-muted w-100" type="submit">Sign in with another account</button>
                </form>
            </div>
        </section>
    </main>
<script src="{{ asset('js/api-browser.js') }}" defer></script>
<script src="{{ asset('js/admin-api.js') }}" defer></script>
</body>
</html>
