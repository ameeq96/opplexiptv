<section class="pricing-section style-two {{ !empty($useSectionSkeletons) ? 'skeleton-section skeleton-section--pricing' : '' }}"
    @if (!empty($useSectionSkeletons)) data-skeleton-section @endif
    id="pricing-section" aria-label="IPTV Pricing Plans and Reseller Packages">
    @if (!empty($useSectionSkeletons))
        <div class="section-skeleton__overlay" aria-hidden="true">
            <div class="section-skeleton__content">
                <span class="section-skeleton__pill"></span>
                <span class="section-skeleton__line section-skeleton__line--lg"></span>
                <span class="section-skeleton__line section-skeleton__line--md"></span>
                <div class="section-skeleton__cards">
                    <span class="section-skeleton__card"></span>
                    <span class="section-skeleton__card"></span>
                    <span class="section-skeleton__card"></span>
                    <span class="section-skeleton__card"></span>
                </div>
            </div>
        </div>
    @endif
    <div class="auto-container">

        <div class="{{ $containerClass ?? 'container' }}">
            @unless ($isMobile ?? false)
                <div class="separator"></div>
            @endunless

            <h3><b>{{ $pricingSection['heading'] ?? __('messages.pricing_heading') }}</b></h3>

            @unless (request()->is('packages') || request()->is('pricing') || request()->is('reseller-panel'))
                <h2 class="h4">{{ $pricingSection['subheading'] ?? __('messages.pricing_subheading') }}</h2>
            @endunless
        </div>

        <style>

            .pricing-section.style-two .separator {
                width: 78px;
                height: 6px;
                margin: 0 auto 22px;
                border-radius: 999px;
                background: linear-gradient(90deg, #ff2e18 0%, #ff4d3a 100%);
            }

            .pricing-section.style-two h1.h2 {
                margin: 0;
                color: #0f172a;
                font-size: clamp(34px, 4vw, 58px);
                line-height: 1.03;
                letter-spacing: -.04em;
                text-align: center;
            }

            .pricing-section.style-two h2.h4 {
                margin: 14px 0 0;
                color: #475569;
                font-size: 20px;
                font-weight: 500;
                line-height: 1.5;
                text-align: center;
            }

            .vendor-toggle,
            .vendor-toggle-reseller {
                display: inline-flex;
                gap: .4rem;
                background: #edf3ff;
                border: 1px solid #dbe7fb;
                border-radius: 16px;
                padding: .4rem;
            }

            .pricing-controls {
                gap: 1rem;
                flex-wrap: wrap;
                margin-top: 28px !important;
                margin-bottom: 26px !important;
            }

            /* Prevent initial flash/LCP shift: hide secondary vendors + reseller by default (JS toggles later) */
            .pkg-item[data-type="iptv"][data-vendor="starshare"],
            .pkg-item[data-type="reseller"],
            #resellerPackages,
            #vendorToggleReseller,
            #creditInfo {
                display: none;
            }

            #real-toggle label.form-switch {
               font-size: 15px !important;
               justify-content: start !important;
               color: #0f172a;
               font-weight: 600;
            }

            #real-toggle label.form-switch span {
                white-space: normal;
                line-height: 1.3;
            }

            .vendor-toggle .tg,
            .vendor-toggle-reseller .tg {
                border: 0;
                background: transparent;
                padding: .7rem 1.1rem;
                border-radius: 12px;
                font-weight: 700;
                color: #1e293b;
                cursor: pointer;
                line-height: 1.2;
            }

            .vendor-toggle .tg.active,
            .vendor-toggle-reseller .tg.active {
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                color: #fff;
                box-shadow: 0 10px 24px rgba(37, 99, 235, .24);
            }

            .pkg-item .price-block,
            .pkg-item .inner-box {
                height: 100%;
            }

            .pkg-item .inner-box {
                display: flex;
                flex-direction: column;
            }

            .pkg-item .lower-box {
                flex: 1;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }

            /* Reserve space for plan icons to prevent CLS */
            .pkg-item .icon-list img {
                width: 48px;
                height: 48px;
                flex-shrink: 0;
            }

            .price-block {
                min-height: 100%;
            }

            .pricing-section.style-two .price-block .inner-box {
                border-radius: 30px;
                border: 1px solid #e8edf5;
                background: linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
                box-shadow: 0 20px 50px rgba(15, 23, 42, .08);
                overflow: hidden;
            }

            .pricing-section.style-two .price-block .upper-box {
                padding: 34px 26px 28px;
                background: radial-gradient(circle at top center, rgba(255, 70, 52, .16) 0%, rgba(255, 70, 52, 0) 56%) !important;
            }

            .pricing-section.style-two .price-block .icon-list {
                justify-content: center;
                margin-bottom: 18px;
            }

            .pricing-section.style-two .price-block .icon-list li {
                width: 86px;
                height: 86px;
                padding: 0;
                border-radius: 50%;
                background: #ffffff;
                box-shadow: 0 16px 34px rgba(15, 23, 42, .08);
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .pricing-section.style-two .price-block .upper-box h4 {
                margin: 0;
                color: #0f172a;
                font-size: 24px;
                font-weight: 800;
                line-height: 1.14;
                letter-spacing: -.03em;
            }

            .pricing-section.style-two .price-block .upper-box h4 span {
                margin-top: 10px;
                color: #ff2e18;
                font-size: 18px;
                font-weight: 800;
                line-height: 1.3;
            }

            .pricing-section.style-two .price-block .lower-box {
                padding: 0 30px 30px;
            }

            .pricing-section.style-two .price-block .price-list li {
                margin-bottom: 16px;
                color: #1e293b;
                font-size: 15px;
                font-weight: 500;
                line-height: 1.45;
            }

            .pricing-section.style-two .price-block .price-list li:before {
                color: #ff2e18;
                font-weight: 700;
            }

            .pricing-section.style-two .package-price-button,
            .pricing-section.style-two .button-box-2 {
                gap: 14px;
                margin-top: 12px;
            }

            .pricing-section.style-two .price-block .button-box .theme-btn {
                min-width: 0;
                flex: 1 1 auto;
                border-radius: 16px;
                padding: 16px 20px;
                background: linear-gradient(135deg, #ff1d09 0%, #e10600 100%);
                box-shadow: none;
            }

            .pricing-section.style-two .price-block .button-box .theme-btn:before {
                display: none;
            }

            .pricing-section.style-two .price-block .button-box .theme-btn .txt {
                color: #ffffff;
                font-size: 18px;
                font-weight: 800;
            }

            .pricing-section.style-two .price-block .button-box .whatsapp {
                width: 42px;
                height: 42px;
                border-radius: 50%;
                box-shadow: 0 10px 22px rgba(34, 197, 94, .18);
            }

            #creditInfo {
                padding: 18px 22px;
                border-radius: 20px;
                background: #fff4f2;
                border: 1px solid #ffd7d1;
            }

            #creditInfo p {
                margin: 0;
                color: #0f172a;
                font-size: 15px;
                line-height: 1.7;
            }

            #normalPackages.scroll-wrapper.normal-wrapper,
            #resellerPackages .reseller-wrapper {
                display: grid !important;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 24px;
                overflow: visible;
                scroll-snap-type: none;
                padding-bottom: 0;
            }

            #normalPackages .pkg-item,
            #resellerPackages .reseller-wrapper .pkg-item {
                width: auto !important;
                min-width: 0 !important;
                max-width: none !important;
                flex: initial !important;
            }

            @media (max-width: 991px) {

                #normalPackages.scroll-wrapper.normal-wrapper,
                #resellerPackages .reseller-wrapper {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }
            }

            @media (max-width: 992px) {

                #normalPackages.scroll-wrapper.normal-wrapper,
                #resellerPackages .reseller-wrapper {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 576px) {

                #normalPackages.scroll-wrapper.normal-wrapper,
                #resellerPackages .reseller-wrapper {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-width: 768px) {

                #normalPackages.scroll-wrapper.normal-wrapper,
                #resellerPackages .reseller-wrapper {
                    display: grid;
                    grid-auto-flow: column;
                    grid-auto-columns: minmax(85%, 1fr);
                    overflow-x: auto;
                    scroll-snap-type: x mandatory;
                    overscroll-behavior-x: contain;
                    padding-bottom: 12px;
                    gap: 16px;
                }

                .pkg-item {
                    scroll-snap-align: start;
                    min-width: 85vw;
                    max-width: 85vw;
                }

                .price-block {
                    height: 100%;
                }
            }

            @media (max-width: 991px) {
                .pricing-section.style-two {
                    padding: 72px 0 50px;
                }

                .pricing-section.style-two .price-block .inner-box {
                    border-radius: 24px;
                }

                .pricing-section.style-two .price-block .upper-box {
                    padding: 28px 18px 24px;
                }

                .pricing-section.style-two .price-block .lower-box {
                    padding: 0 20px 22px;
                }
            }
        </style>

        <div class="pricing-controls d-flex align-items-center justify-content-between mb-3 mt-3">
            <div id="real-toggle">
                <label class="form-switch m-0">
                    <input type="checkbox" id="resellerToggle">
                    <i></i>
                    <span>{{ $pricingSection['show_reseller_label'] ?? __('messages.show_reseller_packages') }}</span>
                </label>
            </div>

            <div id="vendorToggle" class="vendor-toggle" role="tablist" aria-label="Choose IPTV vendor">
                <button type="button" class="tg active" data-vendor="opplex" aria-pressed="true">Opplex</button>
                <button type="button" class="tg" data-vendor="starshare" aria-pressed="false">Starshare</button>
            </div>

            <div id="vendorToggleReseller" class="vendor-toggle-reseller" role="tablist"
                aria-label="Choose reseller vendor" style="display:none">
                <button type="button" class="tg active" data-vendor="opplex" aria-pressed="true">Opplex</button>
                <button type="button" class="tg" data-vendor="starshare" aria-pressed="false">Starshare</button>
            </div>
        </div>

        <div id="creditInfo" class="sec-title centered mb-4" style="display:none">
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

        <div class="scroll-wrapper normal-wrapper" id="normalPackages">
            @foreach ($packages as $package)
                @php
                    $vendorRaw = strtolower(data_get($package, 'vendor', 'opplex'));
                    $vendorRaw = in_array($vendorRaw, ['opplex', 'starshare']) ? $vendorRaw : 'opplex';
                    $vendorKey = $vendorRaw;

                    $plainPrice = trim(strip_tags(data_get($package, 'price', '')));
                    preg_match_all('/\d+(?:\.\d+)?/', $plainPrice, $m);
                    $buyPrice = $m[0] ? end($m[0]) : null;

                    // Remove text in parentheses + embedded price from title.
                    $rawTitle = (string) data_get($package, 'title', '');
                    $titleNoParen = (string) preg_replace('/\s*\([^)]*\)/', '', $rawTitle);
                    $titleBase = trim((string) preg_replace('/\s*-\s*\$?\d+(?:\.\d+)?/i', '', $titleNoParen, 1));
                    $displayTitle = $titleBase;
                @endphp

                <div class="price-block scroll-item pkg-item" data-type="iptv" data-vendor="{{ $vendorKey }}">
                    <div class="inner-box custom-color">
                        <div class="upper-box"
                            @unless ($isMobile ?? false) style="background-image:url('{{ asset('images/background/pattern-4.webp') }}');" @endunless>
                            <ul class="icon-list">
                                <li><span class="icon"><img src="{{ asset('images/icons/service-1.svg') }}"
                                            alt="IPTV" width="48" height="48"></span></li>
                            </ul>
                            <h4>{{ $displayTitle }}<span>{!! $package['price'] ?? '' !!}</span></h4>
                        </div>

                        <div class="lower-box">
                            @if (!empty($package['features']))
                                <ul class="price-list">
                                    @foreach ($package['features'] as $feature)
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
                                    ]) }}"
                                    class="theme-btn btn-style-four">
                                    <span class="txt">{{ __('messages.buy_now') }}</span>
                                </a>

                                @if ($buyPrice)
                                    <a  rel="noopener"
                                        href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_package', ['plan' => $displayTitle, 'price' => $buyPrice])) }}">
                                        <img class="whatsapp" src="{{ asset('images/whatsapp.webp') }}" width="32"
                                            height="32" alt="WhatsApp" loading="lazy" />
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div id="resellerPackages" style="display:none" aria-label="Reseller IPTV Packages">
            <div class="reseller-wrapper">
                @foreach ($resellerPlans as $plan)
                    @php
                        $vendorResRaw = strtolower(data_get($plan, 'vendor', 'opplex'));
                        $vendorResKey = in_array($vendorResRaw, ['opplex', 'starshare']) ? $vendorResRaw : 'opplex';

                        $plainPrice = trim(strip_tags($plan['price'] ?? ''));
                        preg_match_all('/\d+(?:\.\d+)?/', $plainPrice, $m);
                        $buyPrice = $m[0] ? end($m[0]) : null;
                        $resellerRawTitle = (string) data_get($plan, 'title', '');
                        $resellerTitleNoParen = (string) preg_replace('/\s*\([^)]*\)/', '', $resellerRawTitle);
                        $resellerDisplayTitle = trim((string) preg_replace('/\s*-\s*\$?\d+(?:\.\d+)?/i', '', $resellerTitleNoParen, 1));
                    @endphp

                    <div class="price-block reseller-price-block pkg-item d-flex flex-column justify-content-between"
                        data-type="reseller" data-vendor="{{ $vendorResKey }}">
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
                                        <li><span class="icon"><img src="{{ asset($primaryResellerIcon) }}" alt="Reseller Icon"
                                                    width="48" height="48"></span></li>
                                    @endif
                                </ul>
                                <h4>{{ $resellerDisplayTitle }}<span>{!! $plan['price'] !!}</span></h4>
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
                                        ]) }}"
                                        class="theme-btn btn-style-four">
                                        <span class="txt">{{ __('messages.buy_now') }}</span>
                                    </a>

                                    <a  rel="noopener"
                                        href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_package', ['plan' => $resellerDisplayTitle, 'price' => $buyPrice])) }}">
                                        <img class="whatsapp" src="{{ asset('images/whatsapp.webp') }}"
                                            width="32" height="32" alt="WhatsApp" loading="lazy" />
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
                normalPackagesWrap.style.setProperty('display', showReseller ? 'none' : 'grid', 'important');
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
            });
        }

        if (resellerToggle) {
            resellerToggle.addEventListener('change', function() {
                renderIptv();
                renderReseller();
            });
        }

        renderIptv();
        renderReseller();
    });
</script>
