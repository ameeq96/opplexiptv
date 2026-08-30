<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"> {{-- ✅ Mobile responsiveness --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            color-scheme: light;
            --admin-ink: #0b1738;
            --admin-red: #e50914;
            --admin-border: #dbe3ee;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            background:
                radial-gradient(circle at 9% 10%, rgba(229, 9, 20, .08), transparent 28%),
                radial-gradient(circle at 92% 88%, rgba(37, 99, 235, .09), transparent 30%),
                #f4f7fb;
            color: var(--admin-ink);
            font-family: Inter, "Segoe UI", Arial, sans-serif;
        }

        .admin-login-page {
            position: relative;
            display: flex;
            min-height: 100vh;
            min-height: 100svh;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 32px;
            isolation: isolate;
        }

        .admin-login-page::before {
            position: absolute;
            inset: 0;
            z-index: -1;
            background-image:
                linear-gradient(rgba(11, 23, 56, .025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(11, 23, 56, .025) 1px, transparent 1px);
            background-size: 48px 48px;
            content: "";
            -webkit-mask-image: linear-gradient(to bottom, transparent, #000 18%, #000 82%, transparent);
            mask-image: linear-gradient(to bottom, transparent, #000 18%, #000 82%, transparent);
        }

        .admin-login-shell {
            display: grid;
            width: min(100%, 1080px);
            min-height: 620px;
            grid-template-columns: minmax(0, .92fr) minmax(0, 1.08fr);
            overflow: hidden;
            border: 1px solid rgba(11, 23, 56, .1);
            border-radius: 32px;
            background: #fff;
            box-shadow: 0 32px 80px rgba(11, 23, 56, .16);
        }

        .admin-login-brand {
            position: relative;
            display: flex;
            min-width: 0;
            flex-direction: column;
            overflow: hidden;
            padding: 46px;
            background:
                radial-gradient(circle at 6% 4%, rgba(229, 9, 20, .28), transparent 31%),
                radial-gradient(circle at 100% 100%, rgba(45, 92, 205, .32), transparent 38%),
                linear-gradient(145deg, #06112b 0%, #0b1738 56%, #102453 100%);
        }

        .admin-login-brand::before {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, .045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .045) 1px, transparent 1px);
            background-size: 42px 42px;
            content: "";
            -webkit-mask-image: linear-gradient(135deg, #000, transparent 72%);
            mask-image: linear-gradient(135deg, #000, transparent 72%);
            pointer-events: none;
        }

        .admin-login-brand__logo {
            position: relative;
            z-index: 1;
            display: block;
            width: min(100%, 245px);
            height: auto;
        }

        .admin-login-visual {
            position: relative;
            z-index: 1;
            display: flex;
            flex: 1 1 auto;
            align-items: center;
            justify-content: center;
            padding-top: 32px;
        }

        .admin-login-visual__window {
            position: relative;
            width: min(100%, 390px);
            aspect-ratio: 1.2;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 25px;
            background: rgba(255, 255, 255, .09);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .1), 0 26px 54px rgba(1, 7, 25, .26);
            backdrop-filter: blur(12px);
        }

        .admin-login-visual__topbar {
            display: flex;
            height: 42px;
            align-items: center;
            gap: 7px;
            padding: 0 16px;
            border-bottom: 1px solid rgba(255, 255, 255, .1);
            background: rgba(255, 255, 255, .045);
        }

        .admin-login-visual__topbar span {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .34);
        }

        .admin-login-visual__topbar span:first-child,
        .admin-login-visual__nav span:first-child {
            background: #ff3540;
        }

        .admin-login-visual__layout {
            display: grid;
            height: calc(100% - 42px);
            grid-template-columns: 72px minmax(0, 1fr);
        }

        .admin-login-visual__nav {
            display: grid;
            align-content: start;
            gap: 13px;
            padding: 22px 17px;
            border-right: 1px solid rgba(255, 255, 255, .08);
            background: rgba(3, 10, 32, .2);
        }

        .admin-login-visual__nav span {
            height: 8px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .16);
        }

        .admin-login-visual__content {
            display: flex;
            min-width: 0;
            flex-direction: column;
            gap: 18px;
            padding: 25px 22px;
        }

        .admin-login-visual__heading {
            width: 54%;
            height: 11px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .62);
        }

        .admin-login-visual__metrics {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .admin-login-visual__metrics span {
            height: 64px;
            border: 1px solid rgba(255, 255, 255, .09);
            border-radius: 14px;
            background: rgba(255, 255, 255, .07);
        }

        .admin-login-visual__chart {
            position: relative;
            flex: 1 1 auto;
            min-height: 92px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .09);
            border-radius: 15px;
            background: rgba(255, 255, 255, .055);
        }

        .admin-login-visual__chart::before {
            position: absolute;
            inset: 24px 18px 18px;
            background: linear-gradient(160deg, transparent 0 14%, #ff3540 14% 17%, transparent 17% 34%, #ff3540 34% 37%, transparent 37% 54%, #ff3540 54% 57%, transparent 57% 73%, #ff3540 73% 76%, transparent 76%);
            content: "";
            opacity: .88;
        }

        .admin-login-visual__lock {
            position: absolute;
            right: -14px;
            bottom: 26px;
            display: inline-flex;
            width: 76px;
            height: 76px;
            align-items: center;
            justify-content: center;
            border: 6px solid #0b1738;
            border-radius: 23px;
            background: linear-gradient(145deg, #ff3540, #c90812);
            color: #fff;
            box-shadow: 0 18px 34px rgba(1, 7, 25, .34);
        }

        .admin-login-visual__lock svg {
            width: 30px;
            height: 30px;
        }

        .admin-login-panel {
            display: flex;
            min-width: 0;
            align-items: center;
            justify-content: center;
            padding: clamp(46px, 5vw, 76px);
            background: #fff;
        }

        .admin-login-form-wrap {
            width: min(100%, 410px);
        }

        .admin-login-mobile-logo {
            display: none;
            width: 210px;
            max-width: 78%;
            height: auto;
            margin-bottom: 38px;
        }

        .admin-login-heading {
            margin-bottom: 34px;
        }

        .admin-login-heading::before {
            display: block;
            width: 58px;
            height: 4px;
            margin-bottom: 20px;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--admin-red) 0 72%, rgba(229, 9, 20, .18) 72%);
            content: "";
        }

        .admin-login-heading h1 {
            margin: 0;
            color: var(--admin-ink);
            font-size: clamp(34px, 4vw, 46px);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -.04em;
        }

        .admin-login-form {
            display: grid;
            gap: 22px;
        }

        .admin-login-field {
            display: grid;
            gap: 9px;
        }

        .admin-login-field label {
            margin: 0;
            color: #263652;
            font-size: 14px;
            font-weight: 700;
        }

        .admin-login-input {
            position: relative;
        }

        .admin-login-input svg {
            position: absolute;
            top: 50%;
            left: 18px;
            width: 20px;
            height: 20px;
            color: #7b8aa2;
            transform: translateY(-50%);
            pointer-events: none;
            transition: color .18s ease;
        }

        .admin-login-input input {
            width: 100%;
            height: 58px;
            padding: 0 18px 0 52px;
            border: 1px solid var(--admin-border);
            border-radius: 15px;
            outline: 0;
            background: #f8fafc;
            color: var(--admin-ink);
            font: inherit;
            font-size: 15px;
            box-shadow: inset 0 1px 2px rgba(15, 23, 42, .025);
            transition: border-color .18s ease, background .18s ease, box-shadow .18s ease;
        }

        .admin-login-input input:hover {
            border-color: #bcc8d8;
            background: #fff;
        }

        .admin-login-input input:focus {
            border-color: var(--admin-red);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(229, 9, 20, .1);
        }

        .admin-login-input:focus-within svg {
            color: var(--admin-red);
        }

        .admin-login-input input.is-invalid {
            border-color: #dc2626;
            background-image: none;
        }

        .admin-login-error {
            color: #c81e1e;
            font-size: 13px;
            line-height: 1.5;
        }

        .admin-login-submit {
            display: inline-flex;
            width: 100%;
            min-height: 58px;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 4px;
            padding: 14px 22px;
            border: 0;
            border-radius: 15px;
            background: linear-gradient(135deg, #ed1722, #bd0710);
            color: #fff;
            font: inherit;
            font-size: 15px;
            font-weight: 800;
            box-shadow: 0 16px 30px rgba(229, 9, 20, .24);
            cursor: pointer;
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
        }

        .admin-login-submit:hover {
            background: linear-gradient(135deg, #ff2934, #cf0914);
            box-shadow: 0 20px 36px rgba(229, 9, 20, .3);
            transform: translateY(-2px);
        }

        .admin-login-submit:focus-visible {
            outline: 3px solid rgba(229, 9, 20, .3);
            outline-offset: 4px;
        }

        .admin-login-submit svg {
            width: 18px;
            height: 18px;
            transition: transform .18s ease;
        }

        .admin-login-submit:hover svg {
            transform: translateX(3px);
        }

        @media (max-width: 860px) {
            .admin-login-page {
                padding: 24px;
            }

            .admin-login-shell {
                width: min(100%, 540px);
                min-height: 0;
                grid-template-columns: minmax(0, 1fr);
            }

            .admin-login-brand {
                display: none;
            }

            .admin-login-panel {
                padding: 52px 44px;
            }

            .admin-login-mobile-logo {
                display: block;
            }
        }

        @media (max-width: 520px) {
            .admin-login-page {
                align-items: stretch;
                padding: 12px;
            }

            .admin-login-shell {
                align-self: center;
                border-radius: 24px;
                box-shadow: 0 24px 58px rgba(11, 23, 56, .14);
            }

            .admin-login-panel {
                padding: 38px 22px;
            }

            .admin-login-mobile-logo {
                width: 185px;
                margin-bottom: 30px;
            }

            .admin-login-heading {
                margin-bottom: 28px;
            }

            .admin-login-heading h1 {
                font-size: 34px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .admin-login-input input,
            .admin-login-input svg,
            .admin-login-submit,
            .admin-login-submit svg {
                transition-duration: .01ms;
            }
        }
    </style>
</head>
<body>
    <main class="admin-login-page" aria-labelledby="admin-login-title">
        <div class="admin-login-shell">
            <section class="admin-login-brand">
                <img class="admin-login-brand__logo" src="{{ asset('images/opplexiptvlogo.webp') }}"
                    alt="Opplex IPTV" width="250" height="75">

                <div class="admin-login-visual" aria-hidden="true">
                    <div class="admin-login-visual__window">
                        <div class="admin-login-visual__topbar">
                            <span></span><span></span><span></span>
                        </div>
                        <div class="admin-login-visual__layout">
                            <div class="admin-login-visual__nav">
                                <span></span><span></span><span></span><span></span>
                            </div>
                            <div class="admin-login-visual__content">
                                <span class="admin-login-visual__heading"></span>
                                <div class="admin-login-visual__metrics">
                                    <span></span><span></span>
                                </div>
                                <span class="admin-login-visual__chart"></span>
                            </div>
                        </div>
                    </div>
                    <span class="admin-login-visual__lock">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="4" y="10" width="16" height="11" rx="3"></rect>
                            <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                        </svg>
                    </span>
                </div>
            </section>

            <section class="admin-login-panel">
                <div class="admin-login-form-wrap">
                    <img class="admin-login-mobile-logo" src="{{ asset('images/opplexiptvlogo.webp') }}"
                        alt="Opplex IPTV" width="250" height="75">

                    <header class="admin-login-heading">
                        <h1 id="admin-login-title">Admin Login</h1>
                    </header>

                    <form class="admin-login-form" method="POST" action="{{ route('admin.login.attempt') }}">
                        @csrf

                        <div class="admin-login-field">
                            <label for="admin-email">Email</label>
                            <div class="admin-login-input">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="14" rx="3"></rect>
                                    <path d="m4 7 8 6 8-6"></path>
                                </svg>
                                <input id="admin-email" type="email" name="email"
                                    class="@error('email') is-invalid @enderror" required>
                            </div>
                            @error('email')
                                <div class="admin-login-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="admin-login-field">
                            <label for="admin-password">Password</label>
                            <div class="admin-login-input">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="4" y="10" width="16" height="11" rx="3"></rect>
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                                </svg>
                                <input id="admin-password" type="password" name="password" required>
                            </div>
                        </div>

                        <div class="admin-login-field">
                            <label for="admin-captcha">Security Check</label>
                            <div class="admin-login-input">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 3 4.5 6v5.5c0 4.6 3.1 7.8 7.5 9.5 4.4-1.7 7.5-4.9 7.5-9.5V6L12 3Z"></path>
                                    <path d="m9.5 12 1.7 1.7 3.5-3.7"></path>
                                </svg>
                                <input id="admin-captcha" type="text" name="captcha"
                                    class="@error('captcha') is-invalid @enderror"
                                    placeholder="{{ __('messages.form.captcha', ['num1' => $num1, 'num2' => $num2]) }}"
                                    required autocomplete="off" inputmode="numeric"
                                    aria-invalid="@error('captcha') true @else false @enderror"
                                    aria-describedby="@error('captcha') admin-captcha-error @enderror">
                            </div>
                            @error('captcha')
                                <div id="admin-captcha-error" class="admin-login-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="admin-login-submit">
                            <span>Login</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14"></path>
                                <path d="m13 6 6 6-6 6"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
