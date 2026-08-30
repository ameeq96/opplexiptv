<section class="pricing-section style-two" id="pricing-section" aria-label="{{ __('document_ui.home.pricing_aria') }}">
    @once
        <style>
            .va-container { transition: opacity .2s ease, visibility .2s ease, transform .2s ease; }
            body.pricing-in-view .whatsapp-icon,
            body.pricing-in-view:not(.va-panel-open) .va-container {
                opacity: 0 !important;
                visibility: hidden !important;
                pointer-events: none !important;
                transform: translateY(10px);
            }
            #pricing-section .pricing-buy-cta {
                display: inline-flex;
                min-height: 52px;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #e32430 0%, #c70e1a 100%) !important;
                border: 1px solid #c70e1a !important;
                box-shadow: 0 12px 24px rgba(199, 14, 26, .2) !important;
                transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
            }
            #pricing-section .pricing-buy-cta .txt { color: #fff !important; }
            #pricing-section .pricing-buy-cta:hover {
                transform: translateY(-1px);
                background: linear-gradient(135deg, #cf1521 0%, #ad0914 100%) !important;
                box-shadow: 0 15px 28px rgba(173, 9, 20, .24) !important;
            }
            #pricing-section .pricing-buy-cta:focus-visible,
            #pricing-section .price-block .button-box > a:not(.pricing-buy-cta):focus-visible,
            #pricing-section .vendor-toggle .tg:focus-visible,
            #pricing-section .vendor-toggle-reseller .tg:focus-visible {
                outline: 3px solid rgba(201, 19, 29, .42) !important;
                outline-offset: 3px;
            }
            .pricing-trial-cta { box-shadow: 0 12px 28px rgba(223, 3, 3, .25); }
            #pricing-section .pricing-controls {
                display: grid !important;
                grid-template-columns: minmax(0, 1fr) auto;
                align-items: center !important;
                gap: 18px;
                margin: 30px 0 26px !important;
                padding: 12px 14px;
                border: 1px solid #e2e8f1;
                border-radius: 18px;
                background: linear-gradient(135deg, #fff 0%, #f7f9fd 100%);
                box-shadow: 0 12px 32px rgba(15, 23, 42, .07);
            }
            #pricing-section #real-toggle { min-width: 0; }
            #pricing-section .form-switch {
                position: relative;
                justify-content: flex-start;
                gap: 12px;
                min-height: 44px;
                color: #17213a;
                font-size: 15px;
                font-weight: 700;
                line-height: 1.4;
            }
            #pricing-section .form-switch input[type="checkbox"] {
                position: absolute;
                display: block !important;
                width: 1px;
                height: 1px;
                margin: 0;
                opacity: 0;
                clip-path: inset(50%);
            }
            #pricing-section .form-switch i {
                flex: 0 0 48px;
                width: 48px;
                height: 28px;
                border: 1px solid #cbd3df;
                background: #d7dce5;
                box-shadow: inset 0 1px 2px rgba(15, 23, 42, .08);
            }
            #pricing-section .form-switch i::before {
                width: 22px;
                height: 22px;
                top: 2px;
                left: 2px;
                box-shadow: 0 2px 6px rgba(15, 23, 42, .2);
            }
            #pricing-section .form-switch input[type="checkbox"]:checked + i {
                border-color: #c9131d;
                background: #d71923;
            }
            #pricing-section .form-switch input[type="checkbox"]:checked + i::before { left: 22px; }
            #pricing-section .form-switch input[type="checkbox"]:focus-visible + i {
                outline: 3px solid rgba(201, 19, 29, .35);
                outline-offset: 3px;
            }
            #pricing-section .vendor-toggle,
            #pricing-section .vendor-toggle-reseller {
                justify-self: end;
                min-height: 52px;
                margin-inline-start: auto;
                gap: 4px;
                padding: 4px;
                border: 1px solid #dce3ee;
                border-radius: 15px;
                background: #eef2f8;
                box-shadow: inset 0 1px 2px rgba(15, 23, 42, .05);
            }
            #pricing-section .vendor-toggle .tg,
            #pricing-section .vendor-toggle-reseller .tg {
                min-width: 88px;
                min-height: 42px;
                padding: 9px 16px;
                border-radius: 11px;
                color: #47546a;
                font-size: 14px;
                font-weight: 800;
                transition: color .18s ease, background .18s ease, box-shadow .18s ease, transform .18s ease;
            }
            #pricing-section .vendor-toggle .tg:hover,
            #pricing-section .vendor-toggle-reseller .tg:hover { color: #111a2e; }
            #pricing-section .vendor-toggle .tg.active,
            #pricing-section .vendor-toggle-reseller .tg.active {
                background: linear-gradient(135deg, #111d4a 0%, #061039 100%);
                color: #fff;
                box-shadow: 0 8px 18px rgba(6, 16, 57, .2);
            }
            #pricing-section #creditInfo {
                margin: 0 0 24px !important;
                padding: 13px 18px;
                border: 1px solid #e1e7f0;
                border-radius: 14px;
                background: #f8faff;
            }
            #pricing-section #creditInfo p {
                margin: 0;
                color: #445169;
                line-height: 1.65;
            }
            #pricing-section #normalPackages.scroll-wrapper.normal-wrapper,
            #pricing-section #resellerPackages .reseller-wrapper {
                align-items: stretch;
                gap: 20px !important;
            }
            #pricing-section #normalPackages .pkg-item,
            #pricing-section #resellerPackages .reseller-wrapper .pkg-item {
                height: 100%;
                padding-bottom: 0;
            }
            #pricing-section .price-block .inner-box.custom-color {
                position: relative;
                display: flex !important;
                height: 100% !important;
                padding-bottom: 0 !important;
                flex-direction: column;
                overflow: hidden;
                border: 1px solid #e1e7f0;
                border-radius: 22px;
                background: linear-gradient(180deg, #fff 0%, #fbfcff 100%) !important;
                box-shadow: 0 14px 34px rgba(15, 23, 42, .08);
                transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease;
            }
            #pricing-section .price-block .inner-box.custom-color::before {
                position: absolute;
                z-index: 2;
                top: 0;
                right: 0;
                left: 0;
                height: 4px;
                border-radius: 22px 22px 0 0;
                background: linear-gradient(90deg, #ed3944 0%, #c80f1b 100%);
                content: "";
            }
            #pricing-section .price-block .upper-box {
                flex: 0 0 auto;
                gap: 0;
                padding: 30px 22px 24px;
                border-bottom: 1px solid #edf0f5;
                background: radial-gradient(circle at 50% 0%, rgba(227, 36, 48, .13) 0%, rgba(227, 36, 48, 0) 62%), linear-gradient(180deg, #fffafb 0%, #fff 100%) !important;
            }
            #pricing-section .price-block .icon-list {
                margin: 0 0 18px !important;
                padding: 0;
            }
            #pricing-section .price-block .icon-list:empty { display: none; }
            #pricing-section .price-block .icon-list li {
                display: inline-flex;
                width: 68px;
                height: 68px;
                align-items: center;
                justify-content: center;
                padding: 0;
                border: 1px solid #e6eaf1;
                border-radius: 20px;
                background: #fff;
                box-shadow: 0 10px 24px rgba(15, 23, 42, .09);
                transform: none !important;
            }
            #pricing-section .price-block .icon-list img {
                width: 38px;
                height: 38px;
                object-fit: contain;
            }
            #pricing-section .price-block .inner-box:hover .icon-list li { transform: none !important; }
            #pricing-section .price-block .upper-box .package-plan-title {
                margin: 0;
                color: #111a2e;
                font-size: 22px;
                font-weight: 800;
                line-height: 1.22;
                letter-spacing: -.02em;
                text-wrap: balance;
            }
            #pricing-section .price-block .upper-box .package-plan-title span {
                display: block;
                margin-top: 8px;
                color: #d71722;
                font-size: 17px;
                font-weight: 800;
                line-height: 1.35;
                letter-spacing: 0;
            }
            #pricing-section .price-block .lower-box {
                display: flex;
                min-height: 0;
                padding: 22px 22px 24px !important;
                flex: 1 1 auto;
                flex-direction: column;
            }
            #pricing-section .price-block .price-list {
                margin: 0;
                padding: 0;
                flex: 1 1 auto;
            }
            #pricing-section .price-block .price-list li {
                margin-bottom: 14px;
                padding: 0 0 0 30px;
                color: #3f4b61;
                font-size: 14px;
                font-weight: 500;
                line-height: 1.55;
                text-align: start;
            }
            #pricing-section .price-block .price-list li::before {
                display: inline-flex;
                width: 19px;
                height: 19px;
                align-items: center;
                justify-content: center;
                inset-inline-start: 0;
                top: 1px;
                border-radius: 50%;
                background: #fff0f1;
                color: #d71722;
                content: "\2713";
                font-family: inherit;
                font-size: 11px;
                font-weight: 900;
                line-height: 1;
            }
            #pricing-section .price-block .button-box {
                gap: 12px;
                margin-top: 22px;
            }
            #pricing-section .price-block .button-box .theme-btn {
                min-width: 0;
                padding: 14px 18px;
                flex: 1 1 auto;
                border-radius: 14px;
            }
            #pricing-section .price-block .button-box .theme-btn .txt {
                color: #fff;
                font-size: 16px;
                font-weight: 800;
            }
            #pricing-section .price-block .button-box > a:not(.pricing-buy-cta) {
                display: inline-flex;
                width: 52px;
                height: 52px;
                flex: 0 0 52px;
                align-items: center;
                justify-content: center;
                border: 1px solid #d9ebdf;
                border-radius: 14px;
                background: #f5fff8;
                box-shadow: 0 8px 20px rgba(22, 163, 74, .1);
                transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
            }
            #pricing-section .price-block .button-box > a:not(.pricing-buy-cta):hover {
                transform: translateY(-1px);
                border-color: #9dd5ad;
                box-shadow: 0 12px 24px rgba(22, 163, 74, .15);
            }
            #pricing-section .price-block .button-box .whatsapp {
                width: 30px;
                height: 30px;
                border-radius: 50%;
                box-shadow: none;
            }
            [dir="rtl"] #pricing-section .price-block .price-list li { padding: 0 30px 0 0; }
            [dir="rtl"] #pricing-section #normalPackages .pkg-item,
            [dir="rtl"] #pricing-section #resellerPackages .pkg-item {
                direction: rtl;
                text-align: right;
            }
            @media (hover: hover) and (pointer: fine) {
                #pricing-section .price-block .inner-box.custom-color:hover {
                    transform: translateY(-6px);
                    border-color: #d7aeb2;
                    box-shadow: 0 22px 46px rgba(15, 23, 42, .13);
                }
            }
            @media (max-width: 768px) {
                #pricing-section #normalPackages.scroll-wrapper.normal-wrapper,
                #pricing-section #resellerPackages .reseller-wrapper {
                    gap: 14px !important;
                    padding: 4px 4px 16px !important;
                    scroll-padding-inline: 4px;
                }
                #pricing-section .price-block .inner-box.custom-color { border-radius: 20px; }
                #pricing-section .price-block .upper-box { padding: 26px 18px 21px; }
                #pricing-section .price-block .lower-box { padding: 20px 18px 21px !important; }
            }
            @media (max-width: 640px) {
                #pricing-section .pricing-controls {
                    grid-template-columns: minmax(0, 1fr);
                    gap: 10px;
                    margin-top: 24px !important;
                    padding: 10px;
                    border-radius: 16px;
                }
                #pricing-section #real-toggle { padding: 0 4px; }
                #pricing-section .vendor-toggle,
                #pricing-section .vendor-toggle-reseller {
                    width: 100%;
                    margin-inline-start: 0;
                    justify-self: stretch;
                }
                #pricing-section .vendor-toggle .tg,
                #pricing-section .vendor-toggle-reseller .tg {
                    min-width: 0;
                    flex: 1 1 50%;
                }
            }
            @media (prefers-reduced-motion: reduce) {
                #pricing-section .price-block .inner-box.custom-color,
                #pricing-section .pricing-buy-cta,
                #pricing-section .price-block .button-box > a:not(.pricing-buy-cta),
                #pricing-section .vendor-toggle .tg,
                #pricing-section .vendor-toggle-reseller .tg {
                    transition: none;
                }
                #pricing-section .price-block .inner-box.custom-color:hover,
                #pricing-section .pricing-buy-cta:hover,
                #pricing-section .price-block .button-box > a:not(.pricing-buy-cta):hover {
                    transform: none;
                }
            }
            .pricing-intro-shell {
                margin: 0 auto;
                text-align: center;
            }
            .pricing-intro__accent {
                width: 64px;
                height: 4px;
                margin: 0 auto 22px;
                border-radius: 999px;
                background: linear-gradient(90deg, #f04444 0%, #dc111b 100%);
                box-shadow: 0 7px 18px rgba(220, 17, 27, .18);
            }
            .pricing-section.style-two .pricing-intro__title {
                max-width: 980px;
                margin: 0 auto;
                color: #111a2e;
                font-size: clamp(30px, 3.15vw, 44px);
                font-weight: 800;
                line-height: 1.14;
                letter-spacing: -.035em;
                text-wrap: balance;
            }
            .pricing-section.style-two .pricing-intro__subtitle {
                max-width: 720px;
                margin: 10px auto 0;
                color: #253047;
                font-size: clamp(18px, 1.8vw, 22px);
                font-weight: 500;
                line-height: 1.45;
                text-wrap: balance;
            }
            .pricing-section.style-two .pricing-intro__description {
                max-width: 790px;
                margin: 18px auto 0;
                color: #5b6881;
                font-size: 16px;
                line-height: 1.75;
                text-wrap: balance;
            }
            .pricing-policy-note {
                display: grid;
                grid-template-columns: 44px minmax(0, 1fr);
                align-items: center;
                gap: 16px;
                margin: 24px auto 0;
                padding: 18px 20px;
                border: 1px solid #f0d9dc;
                border-inline-start: 4px solid #df202a;
                border-radius: 16px;
                background: linear-gradient(135deg, #fffafa 0%, #fff 72%);
                box-shadow: 0 14px 34px rgba(32, 42, 65, .07);
                color: #273249;
                text-align: start;
            }
            .pricing-policy-note__icon {
                display: inline-flex;
                width: 44px;
                height: 44px;
                align-items: center;
                justify-content: center;
                border: 1px solid rgba(223, 32, 42, .16);
                border-radius: 13px;
                background: rgba(223, 32, 42, .08);
                color: #d71923;
            }
            .pricing-policy-note__icon svg {
                width: 22px;
                height: 22px;
            }
            .pricing-policy-note__content { min-width: 0; }
            .pricing-policy-note__title {
                display: block;
                margin: 0 0 4px;
                color: #111a2e;
                font-size: 15px;
                font-weight: 800;
                line-height: 1.45;
            }
            .pricing-policy-note__text {
                margin: 0;
                color: #5b6579;
                font-size: 14px;
                line-height: 1.65;
                overflow-wrap: anywhere;
            }
            [dir="rtl"] .pricing-section.style-two .pricing-intro__title { letter-spacing: 0; }
            @media (max-width: 991px) {
                .pricing-policy-note { grid-template-columns: 44px minmax(0, 1fr); }
            }
            @media (max-width: 580px) {
                .pricing-intro__accent {
                    width: 52px;
                    margin-bottom: 18px;
                }
                .pricing-section.style-two .pricing-intro__title {
                    font-size: 28px;
                    line-height: 1.18;
                }
                .pricing-section.style-two .pricing-intro__subtitle { font-size: 18px; }
                .pricing-section.style-two .pricing-intro__description {
                    margin-top: 14px;
                    font-size: 15px;
                    line-height: 1.65;
                }
                .pricing-policy-note {
                    grid-template-columns: 38px minmax(0, 1fr);
                    gap: 12px;
                    margin-top: 20px;
                    padding: 15px 14px;
                    border-radius: 14px;
                }
                .pricing-policy-note__icon {
                    width: 38px;
                    height: 38px;
                    border-radius: 11px;
                }
                .pricing-policy-note__icon svg {
                    width: 19px;
                    height: 19px;
                }
                .pricing-policy-note__title { font-size: 14px; }
                .pricing-policy-note__text { font-size: 14px; }
            }
        </style>
    @endonce

    @php
        $providedPricingCopy = $pricingCopy ?? ($documentPricing ?? null);
        $isDocumentEnglishPricing = request()->routeIs('home') || is_array($providedPricingCopy);
        $documentPricing = is_array($providedPricingCopy)
            ? $providedPricingCopy
            : ($isDocumentEnglishPricing ? __('document_home.pricing') : []);
        $initialMode = $initialMode ?? 'iptv';
        $showResellerInitially = $initialMode === 'reseller';
        $displayPackages = collect($packages ?? [])->values()->all();
        if ($isDocumentEnglishPricing && empty($displayPackages)) {
            $documentPlanPrices = [
                'monthly' => $documentPricing['plans']['monthly']['price']
                    ?? ('$2.99 / ' . $documentPricing['plans']['monthly']['title']),
                'three_months' => $documentPricing['plans']['three_months']['price']
                    ?? ('$7.99 / ' . $documentPricing['plans']['three_months']['title']),
                'half_yearly' => $documentPricing['plans']['half_yearly']['price']
                    ?? ('$14.99 / ' . $documentPricing['plans']['half_yearly']['title']),
                'yearly' => $documentPricing['plans']['yearly']['price']
                    ?? ('$23.99 / ' . $documentPricing['plans']['yearly']['title']),
            ];

            foreach ($documentPlanPrices as $planKey => $price) {
                $displayPackages[] = [
                    'vendor' => 'opplex',
                    'title' => $documentPricing['plans'][$planKey]['title'],
                    'price' => $price,
                    'duration_months' => match ($planKey) {
                        'three_months' => 3,
                        'half_yearly' => 6,
                        'yearly' => 12,
                        default => 1,
                    },
                    'features' => $documentPricing['plans'][$planKey]['features'],
                ];
            }
        }
    @endphp

    <div class="auto-container">

        <div class="{{ $containerClass ?? 'container' }} pricing-intro-shell">
            @unless ($isMobile ?? false)
                <div class="pricing-intro__accent" aria-hidden="true"></div>
            @endunless

            <h2 class="pricing-intro__title">{{ $isDocumentEnglishPricing ? $documentPricing['heading'] : ($pricingSection['heading'] ?? __('messages.pricing_heading')) }}</h2>

            @unless (request()->is('packages') || request()->is('pricing') || request()->is('reseller-panel'))
                <p class="pricing-intro__subtitle">{{ $isDocumentEnglishPricing ? ($documentPricing['subheading'] ?? ($pricingSection['subheading'] ?? __('messages.pricing_subheading'))) : ($pricingSection['subheading'] ?? __('messages.pricing_subheading')) }}</p>
            @endunless

            @if ($isDocumentEnglishPricing)
                <p class="home-document-pricing__intro pricing-intro__description">{{ $documentPricing['intro'] }}</p>
            @endif

            <aside class="pricing-policy-note" dir="{{ ($isRtl ?? false) ? 'rtl' : 'ltr' }}"
                aria-label="{{ __('messages.final_sale_no_refunds') }}">
                <span class="pricing-policy-note__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round" focusable="false">
                        <path d="M12 3 4.5 6v5.2c0 4.7 3.1 7.8 7.5 9.8 4.4-2 7.5-5.1 7.5-9.8V6L12 3Z" />
                        <path d="M12 8v5" />
                        <circle cx="12" cy="16.5" r=".8" fill="currentColor" stroke="none" />
                    </svg>
                </span>
                <div class="pricing-policy-note__content">
                    <strong class="pricing-policy-note__title">{{ __('messages.final_sale_no_refunds') }}</strong>
                    <p class="pricing-policy-note__text">
                        {{ __('messages.final_sale_confirmed') }}
                        {{ __('messages.final_sale_verify_before_payment') }}
                    </p>
                </div>
            </aside>
        </div>

        

        <div class="pricing-controls d-flex align-items-center justify-content-between mb-3 mt-3">
            <div id="real-toggle">
                <label class="form-switch m-0">
                    <input type="checkbox" id="resellerToggle" @checked($showResellerInitially)>
                    <i></i>
                    <span>{{ $pricingSection['show_reseller_label'] ?? __('messages.show_reseller_packages') }}</span>
                </label>
            </div>

            <div id="vendorToggle" class="vendor-toggle" role="group" aria-label="{{ __('document_ui.home.choose_iptv_vendor') }}"
                @if ($showResellerInitially) style="display:none" @endif>
                <button type="button" class="tg active" data-vendor="opplex" aria-pressed="true">Opplex</button>
                <button type="button" class="tg" data-vendor="starshare" aria-pressed="false">Filex</button>
            </div>

            <div id="vendorToggleReseller" class="vendor-toggle-reseller" role="group"
                aria-label="{{ __('document_ui.home.choose_reseller_vendor') }}" style="display:{{ $showResellerInitially ? 'inline-flex' : 'none' }}">
                <button type="button" class="tg active" data-vendor="opplex" aria-pressed="true">Opplex</button>
                <button type="button" class="tg" data-vendor="starshare" aria-pressed="false">Filex</button>
            </div>
        </div>

        <div id="creditInfo" class="sec-title centered mb-4" style="display:{{ $showResellerInitially ? 'block' : 'none' }}">
            <p><strong>
                {!! $pricingSection['credit_info'] ?? (
                    '<span style="color:red;">1 '.__('messages.credit').'</span> = '.__('messages.1_month').
                    ' &nbsp;<i class="fa fa-plus"></i>&nbsp; '.
                    '<span style="color:red;">5 '.__('messages.credit').'</span> = '.__('messages.6_months').
                    ' &nbsp;<i class="fa fa-plus"></i>&nbsp; '.
                    '<span style="color:red;">10 '.__('messages.credit').'</span> = '.__('messages.12_months')
                ) !!}
            </strong></p>
        </div>

        <div class="scroll-wrapper normal-wrapper" id="normalPackages"
            @if ($showResellerInitially) style="display:none!important" @endif>
            @foreach ($displayPackages as $package)
                @php
                    $vendorRaw = strtolower(data_get($package, 'vendor', 'opplex'));
                    $vendorRaw = in_array($vendorRaw, ['opplex', 'starshare']) ? $vendorRaw : 'opplex';
                    $vendorKey = $vendorRaw;

                    $buyPrice = data_get($package, 'price_amount');
                    if ($buyPrice === null) {
                        $plainPrice = trim(strip_tags(data_get($package, 'price', '')));
                        preg_match_all('/(?:USD\s*)?\$\s*(\d+(?:\.\d+)?)/i', $plainPrice, $priceMatches);
                        $buyPrice = $priceMatches[1] ? end($priceMatches[1]) : null;
                    }
                    $buyPrice = $buyPrice !== null ? number_format((float) $buyPrice, 2, '.', '') : null;

                    // Remove text in parentheses + embedded price from title.
                    $rawTitle = (string) data_get($package, 'title', '');
                    $titleNoParen = (string) preg_replace('/\s*\([^)]*\)/', '', $rawTitle);
                    $titleBase = trim((string) preg_replace('/\s*-\s*\$?\d+(?:\.\d+)?/i', '', $titleNoParen, 1));
                    $displayTitle = $titleBase;
                    $displayPrice = $package['price'] ?? '';
                    $displayFeatures = $package['features'] ?? [];

                    if ($isDocumentEnglishPricing && $vendorKey === 'opplex') {
                        $durationMonths = (int) data_get($package, 'duration_months', 0);
                        $documentPlanKey = match ($durationMonths) {
                            3 => 'three_months',
                            6 => 'half_yearly',
                            12 => 'yearly',
                            default => match (true) {
                                str_contains(strtolower(str_replace('-', ' ', $titleBase)), '3 month') => 'three_months',
                                str_contains(strtolower(str_replace('-', ' ', $titleBase)), 'half'),
                                str_contains(strtolower(str_replace('-', ' ', $titleBase)), '6 month') => 'half_yearly',
                                str_contains(strtolower(str_replace('-', ' ', $titleBase)), 'year'),
                                str_contains(strtolower(str_replace('-', ' ', $titleBase)), '12 month') => 'yearly',
                                default => 'monthly',
                            },
                        };
                        $documentPlan = $documentPricing['plans'][$documentPlanKey] ?? null;

                        if ($documentPlan) {
                            $displayTitle = $documentPlan['title'];
                            $displayPrice = $documentPlan['price'] ?? ($package['price'] ?? '');
                            $displayFeatures = $documentPlan['features'];
                        }
                    }
                @endphp

                <div class="price-block scroll-item pkg-item" data-type="iptv" data-vendor="{{ $vendorKey }}"
                    @if ($vendorKey !== 'opplex') style="display:none!important" @endif
                    data-package-id="{{ data_get($package, 'id') }}"
                    data-plan="{{ $displayTitle }}" data-price="{{ $buyPrice }}">
                    <div class="inner-box custom-color">
                        <div class="upper-box"
                            @unless ($isMobile ?? false) style="background-image:url('{{ asset('images/background/pattern-4.webp') }}');" @endunless>
                            <ul class="icon-list">
                                <li><span class="icon"><img src="{{ asset('images/icons/service-1.svg') }}"
                                            alt="IPTV" width="48" height="48" loading="lazy" decoding="async"></span></li>
                            </ul>
                            <h3 class="package-plan-title">{{ $displayTitle }} <span>{{ $displayPrice }}</span></h3>
                        </div>

                        <div class="lower-box">
                            @if (!empty($displayFeatures))
                                <ul class="price-list">
                                    @foreach ($displayFeatures as $feature)
                                        <li>{{ $feature }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            <div class="button-box package-price-button d-flex align-items-center">
                                <a  rel="noopener"
                                    href="{{ route('configure', [
                                        'price' => $buyPrice,
                                        'ptype' => 'iptv',
                                        'plan' => $displayTitle,
                                        'vendor' => $vendorKey,
                                        'package_id' => data_get($package, 'id'),
                                    ]) }}"
                                    class="theme-btn btn-style-four pricing-buy-cta" data-package-buy>
                                    <span class="txt">{{ __('messages.buy_now') }}</span>
                                </a>

                                @if ($buyPrice)
                                    <a  rel="noopener"
                                        href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_package', ['plan' => $displayTitle, 'price' => $buyPrice])) }}">
                                        <img class="whatsapp" src="{{ asset('images/whatsapp.webp') }}" width="32"
                                            height="32" alt="WhatsApp" loading="lazy" decoding="async" />
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div id="resellerPackages" style="display:{{ $showResellerInitially ? 'block' : 'none' }}"
            aria-label="{{ __('document_ui.home.reseller_packages_aria') }}">
            <div class="reseller-wrapper">
                @foreach ($resellerPlans as $plan)
                    @php
                        $vendorResRaw = strtolower(data_get($plan, 'vendor', 'opplex'));
                        $vendorResKey = in_array($vendorResRaw, ['opplex', 'starshare']) ? $vendorResRaw : 'opplex';

                        $buyPrice = data_get($plan, 'price_amount');
                        if ($buyPrice === null) {
                            $plainPrice = trim(strip_tags($plan['price'] ?? ''));
                            preg_match_all('/(?:USD\s*)?\$\s*(\d+(?:\.\d+)?)/i', $plainPrice, $priceMatches);
                            $buyPrice = $priceMatches[1] ? end($priceMatches[1]) : null;
                        }
                        $buyPrice = $buyPrice !== null ? number_format((float) $buyPrice, 2, '.', '') : null;
                        $resellerRawTitle = (string) data_get($plan, 'title', '');
                        $resellerTitleNoParen = (string) preg_replace('/\s*\([^)]*\)/', '', $resellerRawTitle);
                        $resellerDisplayTitle = trim((string) preg_replace('/\s*-\s*\$?\d+(?:\.\d+)?/i', '', $resellerTitleNoParen, 1));
                    @endphp

                    <div class="price-block reseller-price-block pkg-item d-flex flex-column justify-content-between"
                        data-type="reseller" data-vendor="{{ $vendorResKey }}"
                        data-package-id="{{ data_get($plan, 'id') }}" data-plan="{{ $resellerDisplayTitle }}" data-price="{{ $buyPrice }}">
                        <div class="inner-box custom-color">
                            <div class="upper-box"
                                @unless ($isMobile ?? false)
                                    style="background-image:url('{{ asset('images/background/pattern-4.webp') }}');"
                                @endunless>
                                <ul class="icon-list">
                                    @php
                                        $primaryResellerIcon = collect($plan['icons'] ?? [])->first();
                                    @endphp
                                    @if ($primaryResellerIcon)
                                        <li><span class="icon"><img src="{{ asset($primaryResellerIcon) }}" alt="{{ __('document_ui.home.reseller_icon_alt') }}"
                                                    width="48" height="48" loading="lazy" decoding="async"></span></li>
                                    @endif
                                </ul>
                                <h3 class="package-plan-title">{{ $resellerDisplayTitle }}<span>{!! $plan['price'] !!}</span></h3>
                            </div>

                            <div class="lower-box">
                                <ul class="price-list">
                                    @foreach ($plan['features'] ?? [] as $feature)
                                        <li>{{ $feature }}</li>
                                    @endforeach
                                </ul>

                                <div class="button-box button-box-2 d-flex align-items-center">
                                    <a  rel="noopener"
                                        href="{{ route('configure', [
                                            'price' => $buyPrice,
                                            'ptype' => 'reseller',
                                            'plan' => $resellerDisplayTitle,
                                            'vendor' => $vendorResKey,
                                            'package_id' => data_get($plan, 'id'),
                                        ]) }}"
                                        class="theme-btn btn-style-four pricing-buy-cta" data-package-buy>
                                        <span class="txt">{{ __('messages.buy_now') }}</span>
                                    </a>

                                    <a  rel="noopener"
                                        href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_package', ['plan' => $resellerDisplayTitle, 'price' => $buyPrice])) }}">
                                        <img class="whatsapp" src="{{ asset('images/whatsapp.webp') }}"
                                            width="32" height="32" alt="WhatsApp" loading="lazy" decoding="async" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const resellerToggle = document.getElementById('resellerToggle');
        const iptvVendorToggle = document.getElementById('vendorToggle');
        const resellerVendorToggle = document.getElementById('vendorToggleReseller');

        const normalPackagesWrap = document.getElementById('normalPackages');
        const resellerWrap = document.getElementById('resellerPackages');
        const creditInfo = document.getElementById('creditInfo');

        const iptvCards = document.querySelectorAll('.pkg-item[data-type="iptv"]');
        const resellerCards = document.querySelectorAll('.pkg-item[data-type="reseller"]');

        const norm = s => (s || '').toString().trim().toLowerCase();
        const isMobilePricing = () => window.matchMedia('(max-width: 768px)').matches;
        const getNormalPackagesDisplay = () => (isMobilePricing() ? 'flex' : 'grid');
        const track = (name, params, metaEvent) => {
            if (typeof window.trackMarketingEvent === 'function') {
                window.trackMarketingEvent(name, params, metaEvent);
            }
        };

        function getActiveVendor(toggleEl, fallback = 'opplex') {
            if (!toggleEl) return fallback;
            const activeBtn = toggleEl.querySelector('.tg.active');
            return activeBtn ? norm(activeBtn.dataset.vendor) : fallback;
        }

        function renderIptv() {
            const showReseller = resellerToggle && resellerToggle.checked;
            const vendor = getActiveVendor(iptvVendorToggle);
            iptvCards.forEach(card => {
                const cardVendor = norm(card.dataset.vendor);
                const show = !showReseller && cardVendor === vendor;
                card.style.setProperty('display', show ? 'block' : 'none', 'important');
            });
        }

        function renderReseller() {
            const showReseller = resellerToggle && resellerToggle.checked;
            const vendor = getActiveVendor(resellerVendorToggle);
            let visibleCount = 0;

            if (normalPackagesWrap) {
                normalPackagesWrap.style.setProperty('display', showReseller ? 'none' : getNormalPackagesDisplay(), 'important');
            }
            if (resellerWrap) {
                resellerWrap.style.setProperty('display', showReseller ? 'block' : 'none', 'important');
            }
            if (creditInfo) {
                creditInfo.style.setProperty('display', showReseller ? 'block' : 'none', 'important');
            }

            if (iptvVendorToggle) {
                iptvVendorToggle.style.setProperty('display', showReseller ? 'none' : 'inline-flex', 'important');
            }
            if (resellerVendorToggle) {
                resellerVendorToggle.style.setProperty('display', showReseller ? 'inline-flex' : 'none', 'important');
            }

            resellerCards.forEach(card => {
                const cardVendor = norm(card.dataset.vendor);
                const showCard = showReseller && cardVendor === vendor;
                if (showCard) visibleCount += 1;
                card.style.setProperty('display', showCard ? 'block' : 'none', 'important');
            });

            if (showReseller && visibleCount === 0) {
                resellerCards.forEach(card => {
                    card.style.setProperty('display', 'block', 'important');
                });
            }

            if (showReseller) {
                iptvCards.forEach(card => {
                    card.style.setProperty('display', 'none', 'important');
                });
            }
        }

        function trackVisiblePackages() {
            const type = resellerToggle && resellerToggle.checked ? 'reseller' : 'iptv';
            const visibleCards = Array.from(type === 'reseller' ? resellerCards : iptvCards)
                .filter(card => card.style.display !== 'none');
            const items = visibleCards.map(card => ({
                item_id: card.dataset.packageId || [card.dataset.vendor, card.dataset.plan].join('-'),
                item_name: card.dataset.plan || '',
                item_brand: norm(card.dataset.vendor) === 'starshare' ? 'Filex' : 'Opplex',
                item_category: type,
                price: Number(card.dataset.price || 0),
                quantity: 1
            }));

            if (items.length) {
                track('view_item_list', {
                    item_list_id: 'pricing',
                    currency: @json(config('services.app.default_currency', 'USD')),
                    items: items
                });
            }
        }

        if (iptvVendorToggle) {
            iptvVendorToggle.addEventListener('click', function(e) {
                const btn = e.target.closest('.tg');
                if (!btn) return;

                iptvVendorToggle.querySelectorAll('.tg').forEach(b => {
                    b.classList.remove('active');
                    b.setAttribute('aria-pressed', 'false');
                });
                btn.classList.add('active');
                btn.setAttribute('aria-pressed', 'true');

                renderIptv();
                trackVisiblePackages();
                track('select_content', {
                    content_type: 'iptv_provider',
                    item_id: norm(btn.dataset.vendor)
                });
            });
        }

        if (resellerVendorToggle) {
            resellerVendorToggle.addEventListener('click', function(e) {
                const btn = e.target.closest('.tg');
                if (!btn) return;

                resellerVendorToggle.querySelectorAll('.tg').forEach(b => {
                    b.classList.remove('active');
                    b.setAttribute('aria-pressed', 'false');
                });
                btn.classList.add('active');
                btn.setAttribute('aria-pressed', 'true');

                renderReseller();
                trackVisiblePackages();
                track('select_content', {
                    content_type: 'reseller_provider',
                    item_id: norm(btn.dataset.vendor)
                });
            });
        }

        if (resellerToggle) {
            resellerToggle.addEventListener('change', function() {
                renderIptv();
                renderReseller();
                trackVisiblePackages();
                track('select_content', {
                    content_type: 'package_type',
                    item_id: resellerToggle.checked ? 'reseller' : 'iptv'
                });
            });
        }

        document.querySelectorAll('[data-package-buy]').forEach(link => {
            link.addEventListener('click', function() {
                const card = link.closest('.pkg-item');
                if (!card) return;
                const item = {
                    item_id: card.dataset.packageId || [card.dataset.vendor, card.dataset.plan].join('-'),
                    item_name: card.dataset.plan || '',
                    item_brand: card.dataset.vendor === 'starshare' ? 'Filex' : 'Opplex',
                    item_category: card.dataset.type || 'iptv',
                    price: Number(card.dataset.price || 0),
                    quantity: 1
                };
                track('select_item', {
                    item_list_id: 'pricing',
                    currency: @json(config('services.app.default_currency', 'USD')),
                    value: item.price,
                    items: [item]
                });
            });
        });

        window.addEventListener('resize', function() {
            renderIptv();
            renderReseller();
        });

        renderIptv();
        renderReseller();
        trackVisiblePackages();

        const pricingSection = document.getElementById('pricing-section');
        if (pricingSection && 'IntersectionObserver' in window) {
            const pricingObserver = new IntersectionObserver(entries => {
                const isVisible = entries.some(entry => entry.isIntersecting);
                document.body.classList.toggle('pricing-in-view', isVisible);
                if (isVisible) {
                    try { sessionStorage.setItem('marketing.pricingViewed', '1'); } catch (e) {}
                    window.dispatchEvent(new CustomEvent('pricing:viewed'));
                }
            });
            pricingObserver.observe(pricingSection);
        }

    });
</script>
