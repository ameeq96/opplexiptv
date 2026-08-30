<!-- ======= Pro Footer v2 ======= -->
@once
<style id="fx-footer-professional-styles">
    .fx-footer {
        --fx-bg: #090e1b;
        --fx-card: rgba(255, 255, 255, .055);
        --fx-fg: #f7f9ff;
        --fx-muted: #aeb8cf;
        --fx-border: rgba(255, 255, 255, .1);
        --fx-accent: #f23a43;
        isolation: isolate;
        border-top: 1px solid rgba(255, 255, 255, .08);
        background:
            radial-gradient(circle at 8% -10%, rgba(242, 58, 67, .14), transparent 30%),
            radial-gradient(circle at 92% 0, rgba(59, 130, 246, .09), transparent 27%),
            linear-gradient(180deg, #0b1020 0%, var(--fx-bg) 100%);
    }
    .fx-footer .fx-footer__dots { opacity: .035; }
    .fx-footer .fx-footer__grad { opacity: .48; }
    .fx-footer .fx-container {
        position: relative;
        z-index: 1;
        max-width: 1280px;
        padding: 46px 32px 28px;
    }
    .fx-footer .fx-footer__head {
        align-items: center;
        gap: 32px;
        padding: 0 0 30px;
        border-bottom-color: var(--fx-border);
    }
    .fx-footer .fx-brand { max-width: 560px; }
    .fx-footer .fx-brand__logo {
        width: min(250px, 100%);
        filter: drop-shadow(0 8px 18px rgba(0, 0, 0, .28));
    }
    .fx-footer .fx-brand__tag {
        max-width: 540px;
        margin-top: 10px;
        color: var(--fx-muted);
        font-size: .94rem;
        line-height: 1.65;
    }
    .fx-footer .fx-social {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .fx-footer .fx-social__btn {
        width: 46px;
        height: 46px;
        border: 1px solid var(--fx-border);
        border-radius: 13px;
        background: var(--fx-card);
        color: #e9edfa;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .04);
        transition: transform .2s ease, border-color .2s ease, background .2s ease, color .2s ease, box-shadow .2s ease;
    }
    .fx-footer .fx-social__btn svg {
        display: block;
        width: 19px;
        height: 19px;
    }
    .fx-footer .fx-social__btn:hover {
        transform: translateY(-3px);
        border-color: rgba(242, 58, 67, .72);
        background: rgba(242, 58, 67, .13);
        color: #fff;
        box-shadow: 0 12px 26px rgba(0, 0, 0, .2);
        text-decoration: none;
    }
    .fx-footer .fx-social__btn:focus-visible,
    .fx-footer .fx-link:focus-visible,
    .fx-footer .fx-deeplinks a:focus-visible {
        outline: 3px solid rgba(255, 89, 98, .5);
        outline-offset: 3px;
        border-radius: 6px;
    }
    .fx-footer .fx-grid {
        display: grid;
        grid-template-columns: minmax(250px, 1.15fr) minmax(300px, 1.25fr) minmax(150px, .7fr) minmax(190px, .85fr);
        gap: clamp(30px, 4vw, 64px);
        padding: 34px 0 32px;
    }
    .fx-footer .fx-col,
    .fx-footer .fx-col:first-child,
    .fx-footer .fx-col--payments {
        grid-column: auto;
        min-width: 0;
    }
    .fx-footer .fx-title {
        position: relative;
        margin: 0 0 19px;
        padding-bottom: 12px;
        color: #fff;
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: .01em;
    }
    .fx-footer .fx-title::after {
        position: absolute;
        inset-inline-start: 0;
        bottom: 0;
        width: 30px;
        height: 2px;
        border-radius: 999px;
        background: var(--fx-accent);
        content: "";
    }
    .fx-footer .fx-list {
        display: grid;
        gap: 10px;
        margin: 0;
        padding: 0;
    }
    .fx-footer .fx-col--payments .fx-list {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        column-gap: 24px;
    }
    .fx-footer .fx-list__item {
        display: grid;
        grid-template-columns: 40px minmax(0, 1fr);
        align-items: center;
        gap: 12px;
        margin: 0;
        color: #dce2f1;
        line-height: 1.5;
    }
    .fx-footer .fx-list__icon {
        display: inline-flex;
        width: 40px;
        height: 40px;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(242, 58, 67, .2);
        border-radius: 12px;
        background: rgba(242, 58, 67, .08);
        color: #ff6a72;
        line-height: 1;
    }
    .fx-footer .fx-list__icon svg {
        display: block;
        width: 19px;
        height: 19px;
    }
    .fx-footer .fx-link {
        color: #cbd3e5;
        border-bottom: 0;
        font-size: .91rem;
        line-height: 1.55;
        text-decoration: none !important;
        transition: color .18s ease, transform .18s ease;
    }
    .fx-footer .fx-col:not(:first-child) .fx-link {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
    }
    .fx-footer .fx-link:hover {
        color: #fff;
        border-bottom-color: transparent;
        text-decoration: none !important;
    }
    .fx-footer .fx-col:not(:first-child) .fx-link:hover { transform: translateX(3px); }
    .fx-footer .fx-container[dir="rtl"] .fx-col:not(:first-child) .fx-link:hover { transform: translateX(-3px); }
    .fx-footer .fx-footer__bottom {
        display: grid;
        grid-template-columns: minmax(250px, .75fr) minmax(0, 1.35fr);
        align-items: start;
        gap: 32px;
        margin-top: 0;
        padding: 24px 0 0;
        border-top-color: var(--fx-border);
    }
    .fx-footer .fx-copy {
        color: #c9d1e3;
        font-size: .86rem;
        line-height: 1.7;
        text-align: start;
    }
    .fx-footer .fx-legal-note {
        max-width: 720px;
        margin-inline-start: auto;
        color: #929db5;
        font-size: .8rem;
        line-height: 1.7;
        text-align: end;
    }
    .fx-footer .fx-deeplinks {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 7px 20px;
        margin-top: 9px;
        color: #929db5;
        font-size: .75rem;
    }
    .fx-footer .fx-deeplinks a {
        position: relative;
        color: #cbd3e5;
        text-decoration: none !important;
        transition: color .18s ease;
    }
    .fx-footer .fx-deeplinks a + a::before {
        position: absolute;
        top: 50%;
        inset-inline-start: -11px;
        width: 3px;
        height: 3px;
        border-radius: 50%;
        background: #5c667c;
        content: "";
        transform: translateY(-50%);
    }
    .fx-footer .fx-deeplinks a:hover { color: #fff; }
    @media (max-width: 1050px) {
        .fx-footer .fx-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 34px 48px;
        }
        .fx-footer .fx-footer__bottom { grid-template-columns: 1fr; gap: 10px; }
        .fx-footer .fx-legal-note {
            max-width: none;
            margin-inline-start: 0;
            text-align: start;
        }
        .fx-footer .fx-deeplinks { justify-content: flex-start; }
    }
    @media (max-width: 700px) {
        .fx-footer .fx-container { padding: 36px 20px 24px; }
        .fx-footer .fx-footer__head {
            flex-direction: column;
            align-items: center;
            gap: 22px;
            padding-bottom: 26px;
            text-align: center;
        }
        .fx-footer .fx-brand__logo { margin-inline: auto; }
        .fx-footer .fx-social { justify-content: center; }
        .fx-footer .fx-grid {
            grid-template-columns: 1fr;
            gap: 30px;
            padding: 30px 0;
        }
        .fx-footer .fx-col--payments .fx-list { grid-template-columns: 1fr; }
        .fx-footer .fx-footer__bottom { text-align: center; }
        .fx-footer .fx-copy,
        .fx-footer .fx-legal-note { text-align: center; }
        .fx-footer .fx-deeplinks { justify-content: center; }
    }
    @media (prefers-reduced-motion: reduce) {
        .fx-footer .fx-social__btn,
        .fx-footer .fx-link { transition: none; }
        .fx-footer .fx-social__btn:hover,
        .fx-footer .fx-link:hover { transform: none; }
    }
</style>
@endonce
<footer class="fx-footer" data-theme="dark">
@php
    // Safe defaults
    $isMobile = $isMobile ?? false;
    $isRtl    = $isRtl ?? false;

    $footerSettings = $footer['settings'] ?? [];
    $footerLinks = $footer['links'] ?? [];
    $footerSocials = $footer['socials'] ?? [];
    $socialProfiles = !empty($footerSocials) ? $footerSocials : [
        ['platform' => 'Facebook', 'url' => 'https://www.facebook.com/profile.php?id=61565476366548', 'icon_class' => 'fa fa-facebook-f'],
        ['platform' => 'LinkedIn', 'url' => 'https://www.linkedin.com/company/digitalize-store/', 'icon_class' => 'fa fa-linkedin'],
        ['platform' => 'Instagram', 'url' => 'https://www.instagram.com/oplextv/', 'icon_class' => 'fa fa-instagram'],
    ];

    // Translations with fallbacks
    $tPhone   = __('messages.footer_phone');    if ($tPhone   === 'messages.footer_phone')   $tPhone = '+1 (639) 390-3194';
    $tEmail   = __('messages.footer_email_1');  if ($tEmail   === 'messages.footer_email_1') $tEmail = 'info@opplexiptv.com';
    $tAddress = __('messages.footer_address');  if ($tAddress === 'messages.footer_address') $tAddress = 'Saskatoon SK, Canada';
    $tRights  = __('messages.footer_rights');   if ($tRights  === 'messages.footer_rights')  $tRights = 'All Rights Reserved.';
    $waText   = __('messages.whatsapp_footer'); if ($waText   === 'messages.whatsapp_footer')$waText = 'Hello! I need help with Opplex IPTV.';

    $tPhone = $footerSettings['phone'] ?? $tPhone;
    $tEmail = $footerSettings['email'] ?? $tEmail;
    $tAddress = $footerSettings['address'] ?? $tAddress;
    $tRights = $footerSettings['rights_text'] ?? $tRights;
    $brandText = $footerSettings['brand_text'] ?? null;
    $cryptoNote = $footerSettings['crypto_note'] ?? null;
    $legalNote = $footerSettings['legal_note'] ?? null;
    $usesDocumentTranslations = in_array(app()->getLocale(), config('app.locales', ['en']), true);

    if ($usesDocumentTranslations) {
        $cryptoNote = __('document_ui.footer.crypto_note');
        $legalNote = __('document_ui.footer.legal_note');
    }

    $footerLinkLabels = [
        '/' => __('messages.nav_home'),
        '/pricing' => __('messages.nav_pricing'),
        '/packages' => __('messages.nav_packages'),
        '/reseller-panel' => __('messages.nav_reseller'),
        '/movies' => __('messages.nav_movies_series'),
        '/iptv-applications' => __('messages.nav_iptv_apps'),
        '/shop' => __('document_ui.shop.menu_label'),
        '/about' => __('messages.nav_about_us'),
        '/contact' => __('messages.nav_contact'),
        '/faqs' => __('messages.nav_faqs'),
        '/terms-of-service' => __('document_ui.footer.terms'),
        '/privacy-policy' => __('document_ui.footer.privacy'),
        '/refund-policy' => __('document_ui.footer.refund'),
        '/activate' => __('document_ui.footer.activate'),
        '/configure' => __('document_ui.footer.configure'),
        '/checkout' => __('document_ui.footer.checkout'),
        '/thank-you' => __('document_ui.footer.thank_you'),
    ];
    $footerLinkLabel = static function (array $link) use ($footerLinkLabels): string {
        $path = parse_url((string) ($link['url'] ?? ''), PHP_URL_PATH);

        return $footerLinkLabels[$path] ?? (string) ($link['label'] ?? '');
    };
    $footerLinkRoutes = [
        '/' => 'home',
        '/pricing' => 'pricing',
        '/packages' => 'packages',
        '/reseller-panel' => 'reseller-panel',
        '/movies' => 'movies',
        '/iptv-applications' => 'iptv-applications',
        '/shop' => 'shop',
        '/about' => 'about',
        '/contact' => 'contact',
        '/faqs' => 'faqs',
        '/terms-of-service' => 'terms-of-service',
        '/privacy-policy' => 'privacy-policy',
        '/refund-policy' => 'refund-policy',
        '/activate' => 'activate',
        '/configure' => 'configure',
        '/checkout' => 'checkout',
        '/thank-you' => 'thankyou',
    ];
    $footerLinkUrl = static function (array $link) use ($footerLinkRoutes): string {
        $url = (string) ($link['url'] ?? '#');
        $path = parse_url($url, PHP_URL_PATH);

        return isset($footerLinkRoutes[$path]) ? route($footerLinkRoutes[$path]) : $url;
    };

    $routeName = optional(request()->route())->getName();
    $usesDiscountWheel = in_array($routeName, ['home', 'packages', 'pricing', 'iptv-subscription-service'], true);
    $isHomeRoute = $routeName === 'home';
    $isMoviesRoute = $routeName === 'movies';
    $isPackagesRoute = $routeName === 'packages';
    $isBlogsIndexRoute = $routeName === 'blogs.index';
    $isContactRoute = $routeName === 'contact';
    $isIptvSubscriptionRoute = $routeName === 'iptv-subscription-service';
    $isAboutRoute = $routeName === 'about';
    $isResellerPanelRoute = $routeName === 'reseller-panel';
    $usesLegacySiteAssets = !$isHomeRoute
        && !$isMoviesRoute
        && !$isPackagesRoute
        && !$isBlogsIndexRoute
        && !$isContactRoute
        && !$isIptvSubscriptionRoute
        && !$isAboutRoute
        && !$isResellerPanelRoute;
    $targetOptimizedRoutes = ['home', 'packages', 'faqs', 'about', 'contact', 'reseller-panel', 'pricing', 'movies', 'shop', 'blogs.index', 'iptv-subscription-service'];
    $isTargetOptimizedRoute = in_array($routeName, $targetOptimizedRoutes, true);
    $needsJquery = $usesLegacySiteAssets;
    $needsBootstrap = $usesLegacySiteAssets;
    $needsCustomScrollbar = $usesLegacySiteAssets;
    $needsMixItUp = false; // MixItUp not used anywhere: movie filtering uses vanilla JS (applyFilter). Avoids shipping legacy JS.
    $needsFancybox = !$isTargetOptimizedRoute;
    $needsAppear = !$isTargetOptimizedRoute;
    $needsParallax = !$isTargetOptimizedRoute;
    $needsParoller = !$isTargetOptimizedRoute;
    $needsOwlCarousel = !$isTargetOptimizedRoute;
    $needsValidation = $usesLegacySiteAssets;
    $needsPhoneAssets = in_array($routeName, ['checkout', 'digital.checkout.show', 'buynow', 'buynowpanel'], true);
@endphp

    <!-- Background layers -->
    @unless ($isMobile)
        <div class="fx-footer__dots" style="background-image:url('{{ asset('images/background/pattern-13.webp') }}')"></div>
        <div class="fx-footer__grad"></div>
    @endunless

    <div class="fx-container" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <!-- Top: brand + socials -->
        <div class="fx-footer__head">
            <div class="fx-brand">
                <img class="fx-brand__logo"
                     src="{{ asset('images/opplexiptvlogo.webp') }}"
                     alt="Opplex IPTV" width="250" height="65" loading="lazy" decoding="async">
                <p class="fx-brand__tag">
                    {{ $cryptoNote ?? __('document_ui.footer.crypto_note') }}
                </p>
            </div>

            <ul class="fx-social">
                @foreach ($socialProfiles as $social)
                    @php
                        $socialLabel = (string) (($social['platform'] ?? '') ?: __('document_ui.footer.social_profile'));
                        $socialKey = strtolower($socialLabel . ' ' . (string) ($social['icon_class'] ?? ''));
                    @endphp
                    <li>
                        <a href="{{ $social['url'] ?? '#' }}" class="fx-social__btn" aria-label="{{ $socialLabel }}"
                           target="_blank" rel="noopener">
                            @if (str_contains($socialKey, 'facebook'))
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="currentColor">
                                    <path d="M13.5 22v-8.7h2.93l.44-3.4H13.5V7.74c0-.98.27-1.65 1.68-1.65h1.8V3.06a24.1 24.1 0 0 0-2.62-.13c-2.58 0-4.35 1.58-4.35 4.48V9.9H7.1v3.4h2.91V22h3.49Z"/>
                                </svg>
                            @elseif (str_contains($socialKey, 'linkedin'))
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="currentColor">
                                    <path d="M6.58 8.48H3.3V21h3.28V8.48ZM4.94 3A1.91 1.91 0 1 0 4.93 6.82 1.91 1.91 0 0 0 4.94 3ZM9.02 8.48V21h3.27v-6.2c0-1.64.31-3.22 2.34-3.22 2 0 2.03 1.87 2.03 3.32V21h3.28v-6.88c0-3.38-.73-5.98-4.68-5.98-1.9 0-3.17 1.04-3.69 2.03h-.04V8.48H9.02Z"/>
                                </svg>
                            @elseif (str_contains($socialKey, 'instagram'))
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.9">
                                    <rect x="3" y="3" width="18" height="18" rx="5"/>
                                    <circle cx="12" cy="12" r="4"/>
                                    <circle cx="17.4" cy="6.7" r="1" fill="currentColor" stroke="none"/>
                                </svg>
                            @else
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                                </svg>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Mid: grid -->
        <div class="fx-grid">
            <!-- Contact -->
            <div class="fx-col">
                <h4 class="fx-title">{{ __('document_ui.footer.contact') }}</h4>
                <ul class="fx-list">
                    <li class="fx-list__item">
                        <span class="fx-list__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" focusable="false" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 3.09 5.18 2 2 0 0 1 5.08 3h3a2 2 0 0 1 2 1.72c.12.9.33 1.78.62 2.63a2 2 0 0 1-.45 2.11L9 10.71a16 16 0 0 0 4.29 4.29l1.25-1.25a2 2 0 0 1 2.11-.45c.85.29 1.73.5 2.63.62A2 2 0 0 1 22 16.92Z"/>
                            </svg>
                        </span>
                        <a href="https://wa.me/16393903194?text={{ urlencode($waText) }}" target="_blank" rel="noopener" class="fx-link"><bdi>{{ $tPhone }}</bdi></a>
                    </li>
                    <li class="fx-list__item">
                        <span class="fx-list__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" focusable="false" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="5" width="18" height="14" rx="2"/>
                                <path d="m3 7 9 6 9-6"/>
                            </svg>
                        </span>
                        <a href="mailto:info@opplexiptv.com" class="fx-link"><bdi>{{ $tEmail }}</bdi></a>
                    </li>
                    <li class="fx-list__item">
                        <span class="fx-list__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" focusable="false" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/>
                                <circle cx="12" cy="10" r="2.5"/>
                            </svg>
                        </span>
                        <span>{{ $tAddress }}</span>
                    </li>
                </ul>
            </div>

                        <!-- Payments -->
            <div class="fx-col fx-col--payments">
                <h4 class="fx-title">{{ __('document_ui.footer.explore') }}</h4>
                <ul class="fx-list">
                    @foreach (($footerLinks['explore'] ?? []) as $l)
                        <li><a class="fx-link" href="{{ $footerLinkUrl($l) }}">{{ $footerLinkLabel($l) }}</a></li>
                    @endforeach
                    @if (empty($footerLinks['explore']))
                        <li><a class="fx-link" href="{{ route('home') }}">{{ __('messages.nav_home') }}</a></li>
                        <li><a class="fx-link" href="{{ route('pricing') }}">{{ __('messages.nav_pricing') }}</a></li>
                        <li><a class="fx-link" href="{{ route('packages', ['direct' => 1]) }}">{{ __('messages.nav_packages') }}</a></li>
                        <li><a class="fx-link" href="{{ route('reseller-panel') }}">{{ __('messages.nav_reseller') }}</a></li>
                        <li><a class="fx-link" href="{{ route('movies') }}">{{ __('messages.nav_movies_series') }}</a></li>
                        <li><a class="fx-link" href="{{ route('iptv-applications') }}">{{ __('messages.nav_iptv_apps') }}</a></li>
                        <li><a class="fx-link" href="{{ route('shop') }}">{{ __('document_ui.shop.menu_label') }}</a></li>
                    @endif
                </ul>
            </div>

            <!-- Company -->
            <div class="fx-col">
                <h4 class="fx-title">{{ __('document_ui.footer.company') }}</h4>
                <ul class="fx-list">
                    @foreach (($footerLinks['company'] ?? []) as $l)
                        <li><a class="fx-link" href="{{ $footerLinkUrl($l) }}">{{ $footerLinkLabel($l) }}</a></li>
                    @endforeach
                    @if (empty($footerLinks['company']))
                        <li><a class="fx-link" href="{{ route('about') }}">{{ __('messages.nav_about_us') }}</a></li>
                        <li><a class="fx-link" href="{{ route('contact') }}">{{ __('messages.nav_contact') }}</a></li>
                        <li><a class="fx-link" href="{{ route('faqs') }}">{{ __('messages.nav_faqs') }}</a></li>
                    @endif
                </ul>
            </div>

            <!-- Legal -->
            <div class="fx-col">
                <h4 class="fx-title">{{ __('document_ui.footer.legal') }}</h4>
                <ul class="fx-list">
                    @foreach (($footerLinks['legal'] ?? []) as $l)
                        <li><a class="fx-link" href="{{ $footerLinkUrl($l) }}">{{ $footerLinkLabel($l) }}</a></li>
                    @endforeach
                    @if (empty($footerLinks['legal']))
                        <li><a class="fx-link" href="{{ route('terms-of-service') }}">{{ __('document_ui.footer.terms') }}</a></li>
                        <li><a class="fx-link" href="{{ route('privacy-policy') }}">{{ __('document_ui.footer.privacy') }}</a></li>
                        <li><a class="fx-link" href="{{ route('refund-policy') }}">{{ __('document_ui.footer.refund') }}</a></li>
                    @endif
                </ul>
            </div>
        </div>

        <!-- Bottom -->
        <div class="fx-footer__bottom">
            <div class="fx-copy">&copy; 2022 - {{ date('Y') }} <strong>Opplex IPTV</strong>. {{ $tRights }}</div>
            <div class="fx-legal-note">
                {{ $legalNote ?? __('document_ui.footer.legal_note') }}
                <div class="fx-deeplinks">
                    @foreach (($footerLinks['deeplink'] ?? []) as $l)
                        <a href="{{ $footerLinkUrl($l) }}">{{ $footerLinkLabel($l) }}</a>
                    @endforeach
                    @if (empty($footerLinks['deeplink']))
                        <a href="{{ route('activate') }}">{{ __('document_ui.footer.activate') }}</a>
                        <a href="{{ route('configure') }}">{{ __('document_ui.footer.configure') }}</a>
                        <a href="{{ route('checkout') }}">{{ __('document_ui.footer.checkout') }}</a>
                        <a href="{{ route('thankyou') }}">{{ __('document_ui.footer.thank_you') }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- ======= /Pro Footer v2 ======= -->


</div><!-- End pagewrapper -->

@if ($usesDiscountWheel)
    @include('includes.spin-popup')
@endif

<div class="scroll-to-top scroll-to-target" data-target="html" aria-label="{{ __('document_ui.footer.scroll_to_top') }}">
    <span class="fa fa-arrow-up" aria-hidden="true"></span>
</div>

<!-- Scripts: keep order; defer ensures execution after parse (preserves order across tags) -->
@if ($needsJquery)
    <script src="https://code.jquery.com/jquery-1.12.4.min.js" defer></script>
@endif
@if ($needsMixItUp)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mixitup/2.1.10/jquery.mixitup.min.js" defer></script>
@endif
@if ($needsValidation)
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js" defer></script>
@endif
@if ($needsBootstrap)
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" defer></script>
@endif
@if ($needsCustomScrollbar)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.js"
        defer></script>
@endif
@if ($needsFancybox)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js" defer></script>
@endif
@if ($needsAppear)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-appear/0.1/jquery.appear.min.js" defer></script>
@endif
@if ($needsParallax)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parallax/3.1.0/parallax.min.js" defer></script>
@endif
@if ($needsParoller)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/paroller.js/1.4.6/jquery.paroller.min.js" defer></script>
@endif
@if ($needsOwlCarousel)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.1.1/owl.carousel.min.js" defer></script>
@endif
@if ($needsPhoneAssets)
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/js/intlTelInput.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/js/utils.js" defer></script>
@endif

<!-- Local scripts last -->
@if ($usesDiscountWheel)
<script>
    (function () {
        var loaded = false;
        var timer = null;

        function cleanup() {
            window.removeEventListener('pricing:viewed', scheduleDiscountWheel);
            if (timer) window.clearTimeout(timer);
        }

        function pricingWasViewed() {
            try { return sessionStorage.getItem('marketing.pricingViewed') === '1'; }
            catch (e) { return false; }
        }

        function loadDiscountWheel() {
            if (loaded) return;
            if (!pricingWasViewed() || document.body.classList.contains('pricing-in-view') || window.__activeMarketingPrompt) {
                timer = window.setTimeout(loadDiscountWheel, 3000);
                return;
            }
            loaded = true;
            cleanup();

            var s = document.createElement('script');
            s.src = "{{ Vite::asset('resources/js/discount-wheel.js') }}";
            s.type = 'module';
            s.defer = true;
            s.addEventListener('load', function () {
                var overlay = document.getElementById('dw-overlay');
                if (!overlay) return;
                new MutationObserver(function () {
                    if (!overlay.classList.contains('show')) {
                        if (window.__activeMarketingPrompt === 'discount-wheel') window.__activeMarketingPrompt = null;
                        return;
                    }
                    if (document.body.classList.contains('va-panel-open') || window.__activeMarketingPrompt === 'voice-assistant') {
                        if (window.DiscountWheel) window.DiscountWheel.hide();
                        return;
                    }
                    window.__activeMarketingPrompt = 'discount-wheel';
                }).observe(overlay, { attributes: true, attributeFilter: ['class'] });

                new MutationObserver(function () {
                    if (document.body.classList.contains('va-panel-open')) {
                        window.__activeMarketingPrompt = 'voice-assistant';
                        if (overlay.classList.contains('show') && window.DiscountWheel) {
                            window.DiscountWheel.hide();
                        }
                    } else if (window.__activeMarketingPrompt === 'voice-assistant') {
                        window.__activeMarketingPrompt = null;
                    }
                }).observe(document.body, { attributes: true, attributeFilter: ['class'] });
            });
            document.body.appendChild(s);
        }

        function scheduleDiscountWheel() {
            if (timer || loaded) return;
            timer = window.setTimeout(loadDiscountWheel, 10000);
        }

        window.addEventListener('pricing:viewed', scheduleDiscountWheel, { once: true });
        if (pricingWasViewed()) scheduleDiscountWheel();
    })();
</script>
@endif
@if ($usesLegacySiteAssets)
    @vite('resources/js/site.js')
@endif

@if ($isMoviesRoute)
    <script>
        (function () {
            'use strict';

            var fancyboxReady = null;
            var fancyboxTranslations = {
                CLOSE: @json(__('interface.fancybox.close')),
                NEXT: @json(__('interface.fancybox.next')),
                PREV: @json(__('interface.fancybox.previous')),
                ERROR: @json(__('interface.fancybox.error')),
                PLAY_START: @json(__('interface.fancybox.play_start')),
                PLAY_STOP: @json(__('interface.fancybox.play_stop')),
                FULL_SCREEN: @json(__('interface.fancybox.full_screen')),
                THUMBS: @json(__('interface.fancybox.thumbnails')),
                DOWNLOAD: @json(__('interface.fancybox.download')),
                SHARE: @json(__('interface.fancybox.share')),
                ZOOM: @json(__('interface.fancybox.zoom'))
            };
            var trailerUnavailableMessage = @json(__('interface.fancybox.trailer_unavailable'));

            function onReady(fn) {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', fn, { once: true });
                } else {
                    fn();
                }
            }

            function firstChildByTag(parent, tagName) {
                if (!parent) return null;
                tagName = tagName.toUpperCase();
                for (var i = 0; i < parent.children.length; i += 1) {
                    if (parent.children[i].tagName === tagName) return parent.children[i];
                }
                return null;
            }

            function loadScript(src) {
                return new Promise(function (resolve, reject) {
                    var existing = document.querySelector('script[src="' + src + '"]');
                    if (existing) {
                        if (existing.dataset.loaded === 'true') {
                            resolve();
                            return;
                        }
                        existing.addEventListener('load', resolve, { once: true });
                        existing.addEventListener('error', reject, { once: true });
                        return;
                    }

                    var script = document.createElement('script');
                    script.src = src;
                    script.defer = true;
                    script.onload = function () {
                        script.dataset.loaded = 'true';
                        resolve();
                    };
                    script.onerror = reject;
                    document.body.appendChild(script);
                });
            }

            function loadCss(href) {
                if (document.querySelector('link[href="' + href + '"]')) return;
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = href;
                document.head.appendChild(link);
            }

            function ensureFancybox() {
                if (window.jQuery && window.jQuery.fancybox) {
                    return Promise.resolve();
                }

                if (!fancyboxReady) {
                    loadCss('https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css');
                    fancyboxReady = loadScript('https://code.jquery.com/jquery-1.12.4.min.js')
                        .then(function () {
                            return loadScript('https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js');
                        });
                }

                return fancyboxReady;
            }

            function applyFilter(filter) {
                var selector = filter ? filter.getAttribute('data-filter') : 'all';
                if (!selector) selector = 'all';

                document.querySelectorAll('.filter-tabs .filter').forEach(function (item) {
                    item.classList.toggle('active', item === filter);
                });

                document.querySelectorAll('.filter-list .feature-block').forEach(function (card) {
                    var visible = selector === 'all' || card.matches(selector);
                    card.style.display = visible ? 'block' : 'none';
                });
            }

            function initMovieFilters() {
                var filters = document.querySelectorAll('.filter-tabs .filter');
                if (!filters.length) return;

                filters.forEach(function (filter) {
                    filter.addEventListener('click', function () {
                        applyFilter(filter);
                    });

                    filter.addEventListener('keydown', function (event) {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            applyFilter(filter);
                        }
                    });
                });

                applyFilter(document.querySelector('.filter-tabs .filter.active') || filters[0]);
            }

            function initMobileMenu() {
                var source = document.querySelector('.main-header .main-menu .navigation');
                var target = document.querySelector('.mobile-menu .menu-outer');
                if (source && target && !target.querySelector('.navigation')) {
                    target.insertBefore(source.cloneNode(true), target.firstChild);
                }

                document.querySelectorAll('.mobile-menu .navigation li.dropdown').forEach(function (item) {
                    var submenu = firstChildByTag(item, 'ul');
                    if (!submenu || item.querySelector('.dropdown-btn')) return;

                    var button = document.createElement('div');
                    button.className = 'dropdown-btn';
                    button.innerHTML = '<span class="fa fa-angle-down"></span>';
                    item.appendChild(button);

                    var toggle = function (event) {
                        if (event) event.preventDefault();
                        button.classList.toggle('open');
                        submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
                    };

                    button.addEventListener('click', toggle);

                    var link = firstChildByTag(item, 'a');
                    if (link && (link.getAttribute('href') === '#' || link.getAttribute('href') === '')) {
                        link.addEventListener('click', toggle);
                    }
                });

                var open = document.querySelector('.mobile-nav-toggler');
                var backdrop = document.querySelector('.mobile-menu .menu-backdrop');
                var close = document.querySelector('.mobile-menu .close-btn');

                if (open) {
                    open.addEventListener('click', function () {
                        document.body.classList.add('mobile-menu-visible');
                    });
                }

                [backdrop, close].forEach(function (el) {
                    if (!el) return;
                    el.addEventListener('click', function () {
                        document.body.classList.remove('mobile-menu-visible');
                    });
                });
            }

            function initScrollUi() {
                var header = document.querySelector('.main-header');
                var scrollButton = document.querySelector('.scroll-to-target');

                function updateHeader() {
                    if (!header) return;
                    var fixed = window.scrollY >= header.offsetHeight;
                    header.classList.toggle('fixed-header', fixed);
                    if (scrollButton) scrollButton.style.display = fixed ? 'block' : 'none';
                }

                if (scrollButton) {
                    scrollButton.addEventListener('click', function () {
                        var target = scrollButton.getAttribute('data-target') || 'html';
                        var targetEl = document.querySelector(target) || document.documentElement;
                        window.scrollTo({ top: targetEl.offsetTop || 0, behavior: 'smooth' });
                    });
                }

                window.addEventListener('scroll', updateHeader, { passive: true });
                updateHeader();
            }

            function initLightbox() {
                function openTrailer(href) {
                    return ensureFancybox()
                        .then(function () {
                            var defaults = window.jQuery.fancybox.defaults;
                            defaults.i18n = defaults.i18n || {};
                            defaults.i18n.site = fancyboxTranslations;
                            defaults.lang = 'site';

                            window.jQuery.fancybox.open({
                                src: href,
                                type: 'iframe',
                                opts: {
                                    iframe: {
                                        preload: false
                                    }
                                }
                            });
                        })
                        .catch(function () {
                            window.open(href, '_blank', 'noopener');
                        });
                }

                document.addEventListener('click', function (event) {
                    var link = event.target.closest('.lightbox-image');
                    if (!link) return;

                    var href = link.getAttribute('href');
                    var endpoint = link.getAttribute('data-trailer-endpoint');
                    if (!href && !endpoint) return;

                    event.preventDefault();
                    if (!endpoint) {
                        openTrailer(href);
                        return;
                    }

                    if (link.dataset.loading === 'true') return;
                    link.dataset.loading = 'true';

                    fetch(endpoint, {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    })
                        .then(function (response) {
                            if (!response.ok) throw new Error(trailerUnavailableMessage);
                            return response.json();
                        })
                        .then(function (payload) {
                            if (!payload.url) throw new Error(trailerUnavailableMessage);
                            link.setAttribute('href', payload.url);
                            link.removeAttribute('data-trailer-endpoint');
                            openTrailer(payload.url);
                        })
                        .catch(function () {
                            // Keep the endpoint so a transient network failure can be retried.
                        })
                        .finally(function () {
                            delete link.dataset.loading;
                        });
                });
            }

            onReady(function () {
                initMovieFilters();
                initMobileMenu();
                initScrollUi();
                initLightbox();
            });
        })();
    </script>
@endif

<script>
    @if ($needsPhoneAssets)
    (function() {
        const invalidPhoneMessage = @json(__('interface.common.invalid_phone'));
        const countryListLabel = @json(__('interface.phone.country_list_aria'));

        function localizedCountryNames() {
            const countryApi = window.intlTelInputGlobals;
            if (!countryApi || typeof countryApi.getCountryData !== 'function') {
                return {};
            }

            const countryData = countryApi.getCountryData();
            const fallbackNames = countryData.reduce((names, country) => {
                if (country.iso2) names[country.iso2] = country.iso2.toUpperCase();
                return names;
            }, {});

            if (!window.Intl || typeof window.Intl.DisplayNames !== 'function') {
                return fallbackNames;
            }

            try {
                const locale = document.documentElement.lang || 'en';
                if (!window.Intl.DisplayNames.supportedLocalesOf([locale]).length) {
                    return fallbackNames;
                }
                const regionNames = new window.Intl.DisplayNames([locale], { type: 'region' });

                return countryData.reduce((names, country) => {
                    const regionCode = String(country.iso2 || '').toUpperCase();
                    if (!regionCode) return names;

                    try {
                        const localizedName = regionNames.of(regionCode);
                        if (localizedName && localizedName.toUpperCase() !== regionCode) {
                            names[country.iso2] = localizedName;
                        }
                    } catch (e) {}

                    return names;
                }, fallbackNames);
            } catch (e) {
                return fallbackNames;
            }
        }

        function initPhone() {
            const input = document.getElementById('phone');
            const errEl = document.getElementById('phone-client-error');
            if (!input || !window.intlTelInput) return; // plugin not loaded yet

            const iti = window.intlTelInput(input, {
                initialCountry: "pk",
                preferredCountries: ["pk", "sa", "ae", "gb", "us", "ca"],
                separateDialCode: true,
                nationalMode: true,
                placeholderNumberType: "MOBILE",
                localizedCountries: localizedCountryNames(),
                utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@19.5.7/build/js/utils.js"
            });

            const countryList = input.parentElement
                ? input.parentElement.querySelector('.iti__country-list')
                : null;
            if (countryList) countryList.setAttribute('aria-label', countryListLabel);

            if (input.value && input.value.trim().startsWith('+')) {
                try {
                    iti.setNumber(input.value.trim());
                } catch (e) {}
            }

            const showError = (msg) => {
                if (!errEl) return;
                errEl.textContent = msg || '';
                errEl.classList.toggle('d-none', !msg);
            };

            input.addEventListener('blur', () => {
                showError('');
                if (!input.value.trim()) return;
                if (!iti.isValidNumber()) showError(invalidPhoneMessage);
            }, {
                passive: true
            });

            const form = input.closest('form');
            if (form) {
                form.addEventListener('submit', (e) => {
                    showError('');
                    if (input.value.trim() && !iti.isValidNumber()) {
                        e.preventDefault();
                        showError(invalidPhoneMessage);
                        input.focus();
                        return false;
                    }
                    // submit in E.164
                    input.value = iti.getNumber();
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPhone, {
                once: true
            });
        } else {
            initPhone();
        }
    })();
    @endif

    (function() {
        'use strict';

        const usePercentageCarouselOffsets = @json($isHomeRoute || $isAboutRoute || $isResellerPanelRoute);

        const onReady = (fn) => {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', fn, {
                    once: true
                });
            } else {
                fn();
            }
        };

        onReady(() => {
            // Lazy background images
            const lazyBackgrounds = Array.from(document.querySelectorAll('.lazy-background'))
                .filter((element) => !element.closest('[data-carousel-type="hero"]'));

            const applyBg = (el) => {
                const bgUrl = el.getAttribute('data-bg');
                if (bgUrl) el.style.backgroundImage = `url(${bgUrl})`;
            };

            if ('IntersectionObserver' in window) {
                const io = new IntersectionObserver((entries, obs) => {
                    for (const entry of entries) {
                        if (entry.isIntersecting) {
                            applyBg(entry.target);
                            obs.unobserve(entry.target);
                        }
                    }
                }, {
                    rootMargin: '200px 0px'
                });
                lazyBackgrounds.forEach(el => io.observe(el));
            } else {
                // Fallback: apply immediately
                lazyBackgrounds.forEach(applyBg);
            }

            const nativeCarousels = document.querySelectorAll('[data-native-carousel]');

            const bindNativeCarousel = (root) => {
                const viewport = root.querySelector('.native-carousel__viewport');
                const track = root.querySelector('.native-carousel__track');
                const slides = Array.from(root.querySelectorAll('.native-carousel__slide'));
                const prev = root.querySelector('[data-native-prev]');
                const next = root.querySelector('[data-native-next]');
                const dots = Array.from(root.querySelectorAll('[data-native-dot]'));
                const autoplay = parseInt(root.getAttribute('data-autoplay') || '0', 10);
                const isHero = root.getAttribute('data-carousel-type') === 'hero';
                const isRtlCarousel = root.getAttribute('data-rtl') === 'true';

                if (!viewport || !track || slides.length === 0) {
                    return;
                }

                let index = 0;
                let visibleItems = 1;
                let timer = null;
                let startX = 0;
                let isPointerDown = false;

                const getVisibleItems = () => {
                    if (isHero) return 1;

                    if (usePercentageCarouselOffsets) {
                        if (window.matchMedia('(max-width: 767px)').matches) {
                            return parseInt(root.getAttribute('data-items-mobile') || '1', 10);
                        }
                        if (window.matchMedia('(max-width: 1024px)').matches) {
                            return parseInt(root.getAttribute('data-items-tablet') || root.getAttribute('data-items-mobile') || '1', 10);
                        }
                        return parseInt(root.getAttribute('data-items-desktop') || '1', 10);
                    }

                    const width = window.innerWidth;
                    if (width <= 767) return parseInt(root.getAttribute('data-items-mobile') || '1', 10);
                    if (width <= 1024) return parseInt(root.getAttribute('data-items-tablet') || root.getAttribute('data-items-mobile') || '1', 10);
                    return parseInt(root.getAttribute('data-items-desktop') || '1', 10);
                };

                const maxIndex = () => Math.max(0, slides.length - visibleItems);

                const lazyLoadSlide = (slide) => {
                    if (!slide) return;
                    const bg = slide.getAttribute('data-bg');
                    if (bg && !slide.style.backgroundImage) {
                        slide.style.backgroundImage = `url(${bg})`;
                    }
                };

                const updateDots = () => {
                    dots.forEach((dot, dotIndex) => {
                        dot.classList.toggle('is-active', dotIndex === index);
                    });
                };

                const updateArrows = () => {
                    const disabled = slides.length <= visibleItems;
                    if (prev) {
                        prev.classList.toggle('is-hidden', disabled);
                        prev.disabled = disabled;
                    }
                    if (next) {
                        next.classList.toggle('is-hidden', disabled);
                        next.disabled = disabled;
                    }
                };

                const render = () => {
                    visibleItems = getVisibleItems();
                    root.style.setProperty('--native-items', String(visibleItems));
                    root.style.setProperty('--native-gap', `${parseInt(root.getAttribute('data-gap') || '30', 10)}px`);
                    root.style.setProperty('--native-autoplay', `${autoplay}ms`);

                    if (index > maxIndex()) {
                        index = maxIndex();
                    }

                    if (isHero) {
                        slides.forEach((slide, slideIndex) => {
                            const isActive = slideIndex === index;
                            slide.classList.toggle('is-active', isActive);
                            slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
                            if (isActive) lazyLoadSlide(slide);
                        });
                        updateDots();
                        updateArrows();
                        return;
                    }

                    const gap = parseInt(root.getAttribute('data-gap') || '30', 10);
                    const direction = isRtlCarousel ? 1 : -1;

                    if (usePercentageCarouselOffsets) {
                        const offsetPercent = index * (100 / visibleItems);
                        const offsetGap = index * (gap / visibleItems);
                        track.style.transform = `translate3d(${direction * offsetPercent}%, 0, 0) translateX(${direction * offsetGap}px)`;
                    } else {
                        const viewportWidth = viewport.clientWidth;
                        const slideWidth = visibleItems > 0 ? (viewportWidth - (gap * (visibleItems - 1))) / visibleItems : viewportWidth;
                        track.style.transform = `translate3d(${direction * index * (slideWidth + gap)}px, 0, 0)`;
                    }

                    slides.slice(index, index + visibleItems + 1).forEach(lazyLoadSlide);
                    updateArrows();
                };

                const goTo = (nextIndex) => {
                    if (isHero) {
                        if (nextIndex < 0) nextIndex = slides.length - 1;
                        if (nextIndex >= slides.length) nextIndex = 0;
                    } else {
                        if (nextIndex < 0) nextIndex = maxIndex();
                        if (nextIndex > maxIndex()) nextIndex = 0;
                    }

                    index = nextIndex;
                    render();
                    updateDots();
                };

                const stopAutoplay = () => {
                    if (timer) {
                        window.clearInterval(timer);
                        timer = null;
                    }
                };

                const startAutoplay = () => {
                    stopAutoplay();
                    if (!autoplay) return;
                    if (!isHero && slides.length <= visibleItems) return;

                    timer = window.setInterval(() => {
                        goTo(index + 1);
                    }, autoplay);
                };

                if (prev) {
                    prev.addEventListener('click', () => {
                        goTo(index - 1);
                        startAutoplay();
                    });
                }

                if (next) {
                    next.addEventListener('click', () => {
                        goTo(index + 1);
                        startAutoplay();
                    });
                }

                dots.forEach((dot) => {
                    dot.addEventListener('click', () => {
                        goTo(parseInt(dot.getAttribute('data-native-dot') || '0', 10));
                        startAutoplay();
                    });
                });

                if (!isHero) {
                    root.addEventListener('mouseenter', stopAutoplay);
                    root.addEventListener('mouseleave', startAutoplay);
                    root.addEventListener('focusin', stopAutoplay);
                    root.addEventListener('focusout', startAutoplay);
                }

                viewport.addEventListener('pointerdown', (event) => {
                    isPointerDown = true;
                    startX = event.clientX;
                });

                viewport.addEventListener('pointerup', (event) => {
                    if (!isPointerDown) return;
                    const delta = event.clientX - startX;
                    isPointerDown = false;
                    if (Math.abs(delta) < 40) return;
                    const swipeDirection = delta > 0 ? -1 : 1;
                    goTo(index + (isRtlCarousel ? -swipeDirection : swipeDirection));
                    startAutoplay();
                });

                viewport.addEventListener('pointerleave', () => {
                    isPointerDown = false;
                });

                window.addEventListener('resize', render, { passive: true });

                lazyLoadSlide(slides[0]);
                render();
                updateDots();
                startAutoplay();
            };

            nativeCarousels.forEach(bindNativeCarousel);

            const shareButtons = document.querySelectorAll('[data-share-url]');

            shareButtons.forEach((button) => {
                button.addEventListener('click', async () => {
                    const url = button.getAttribute('data-share-url') || window.location.href;
                    const title = button.getAttribute('data-share-title') || document.title;
                    const text = button.getAttribute('data-share-text') || title;
                    const original = button.innerHTML;

                    try {
                        if (navigator.share) {
                            await navigator.share({ title, text, url });
                        } else if (navigator.clipboard && navigator.clipboard.writeText) {
                            await navigator.clipboard.writeText(url);
                            button.innerHTML = '<i class="fa fa-check" aria-hidden="true"></i>';
                            window.setTimeout(() => {
                                button.innerHTML = original;
                            }, 1600);
                        } else {
                            window.prompt(@json(__('interface.common.copy_link_prompt')), url);
                        }
                    } catch (error) {
                        // User cancelled share sheet or clipboard write failed.
                    }
                });
            });

            // Reseller toggle (guard all elements)
            // else: elements not on this page — silently skip
        });
    })();
</script>
