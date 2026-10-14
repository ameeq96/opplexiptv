<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Change Admin Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite('resources/css/admin.css')
</head>
<body class="admin-auth-body">
    <main class="admin-auth-shell" aria-labelledby="admin-password-title">
        <aside class="admin-auth-aside">
            <img class="admin-auth-brand" src="{{ asset('images/opplexiptvlogo.webp') }}"
                alt="Opplex IPTV" width="250" height="75">
            <div>
                <p class="admin-auth-kicker">ACCOUNT SECURITY</p>
                <h2>A fresh password.<br>A protected workspace.</h2>
                <p class="admin-auth-note">Keep your account details private and use a unique password for your admin access.</p>
            </div>
            <p class="admin-auth-note mb-0">Your customers and business deserve careful protection.</p>
        </aside>
        <section class="admin-auth-main">
            <div class="admin-auth-card">
                <header class="mb-4">
                    <p class="admin-auth-kicker">YOUR ACCOUNT</p>
                    <h1 id="admin-password-title">Change your password</h1>
                    <p class="text-muted mb-0">For security, set a new password before continuing to the admin portal.</p>
                </header>

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="admin-auth-field mb-3">
                        <label class="form-label" for="current-password">Current password</label>
                        <input class="form-control" id="current-password" name="current_password" type="password"
                            autocomplete="current-password" required autofocus>
                    </div>

                    <div class="admin-auth-field mb-3">
                        <label class="form-label" for="new-password">New password</label>
                        <input class="form-control" id="new-password" name="password" type="password"
                            autocomplete="new-password" minlength="12" required>
                        <div class="form-text">Use at least 12 characters with upper/lowercase letters, a number and a symbol.</div>
                    </div>

                    <div class="admin-auth-field mb-4">
                        <label class="form-label" for="password-confirmation">Confirm new password</label>
                        <input class="form-control" id="password-confirmation" name="password_confirmation"
                            type="password" autocomplete="new-password" minlength="12" required>
                    </div>

                    <button class="btn btn-primary w-100" type="submit">Update password</button>
                </form>

                <form class="mt-3" method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="btn btn-link text-muted w-100" type="submit">Log out</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
