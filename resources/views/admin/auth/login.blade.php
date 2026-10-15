<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"> {{-- ✅ Mobile responsiveness --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite('resources/css/admin.css')
</head>
<body class="admin-auth-body">
    <main class="admin-auth-shell" aria-labelledby="admin-login-title">
        <aside class="admin-auth-aside">
            <img class="admin-auth-brand" src="{{ asset('images/opplexiptvlogo.webp') }}"
                alt="Opplex IPTV" width="250" height="75">
            <div>
                <p class="admin-auth-kicker">OPPLEX WORKSPACE</p>
                <h2>Your business.<br>One clear view.</h2>
                <p class="admin-auth-note">Manage customers, orders and conversations from your admin workspace.</p>
            </div>
            <p class="admin-auth-note mb-0">For authorized team members only.</p>
        </aside>

        <section class="admin-auth-main">
            <div class="admin-auth-card">
                <header class="mb-4">
                    <p class="admin-auth-kicker">WELCOME BACK</p>
                    <h1 id="admin-login-title">Admin Login</h1>
                    <p class="text-muted mb-0">Sign in to continue to your workspace.</p>
                </header>

                <form method="POST" action="{{ route('admin.login.attempt') }}">
                    @csrf

                    <div class="admin-auth-field mb-3">
                        <label class="form-label" for="admin-email">Email</label>
                        <input id="admin-email" type="email" name="email"
                            class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                            autocomplete="username" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="admin-auth-field mb-3">
                        <label class="form-label" for="admin-password">Password</label>
                        <input id="admin-password" type="password" name="password" class="form-control"
                            autocomplete="current-password" required>
                    </div>

                    <div class="admin-auth-field mb-4">
                        <label class="form-label" for="admin-captcha">Security Check</label>
                        <input id="admin-captcha" type="text" name="captcha"
                            class="form-control @error('captcha') is-invalid @enderror"
                            placeholder="{{ __('messages.form.captcha', ['num1' => $num1, 'num2' => $num2]) }}"
                            required autocomplete="off" inputmode="numeric"
                            aria-invalid="@error('captcha') true @else false @enderror"
                            aria-describedby="@error('captcha') admin-captcha-error @enderror">
                        @error('captcha')
                            <div id="admin-captcha-error" class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </section>
    </main>
<script src="{{ asset('js/api-browser.js') }}" defer></script>
<script src="{{ asset('js/admin-api.js') }}" defer></script>
</body>
</html>
