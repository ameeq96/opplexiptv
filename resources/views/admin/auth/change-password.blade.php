<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Change Admin Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5">
        <div class="card border-0 shadow-sm mx-auto" style="max-width: 560px;">
            <div class="card-body p-4 p-md-5">
                <h1 class="h3 mb-2">Change your password</h1>
                <p class="text-muted mb-4">For security, set a new password before continuing to the admin portal.</p>

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

                    <div class="mb-3">
                        <label class="form-label" for="current-password">Current password</label>
                        <input class="form-control" id="current-password" name="current_password" type="password"
                            autocomplete="current-password" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="new-password">New password</label>
                        <input class="form-control" id="new-password" name="password" type="password"
                            autocomplete="new-password" minlength="12" required>
                        <div class="form-text">Use at least 12 characters with upper/lowercase letters, a number and a symbol.</div>
                    </div>

                    <div class="mb-4">
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
        </div>
    </main>
</body>
</html>
