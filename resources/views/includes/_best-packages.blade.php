<section class="pricing-section style-two" id="pricing-section" aria-label="IPTV Pricing Plans and Reseller Packages">
    <div class="auto-container">

        <div class="{{ $containerClass ?? 'container' }}">
            @unless ($isMobile ?? false)
                <div class="separator"></div>
            @endunless

            <h1 class="h2"><b>{{ __('messages.pricing_heading') }}</b></h1>

            @unless (request()->is('packages') || request()->is('pricing') || request()->is('reseller-panel'))
                <h2 class="h4">{{ __('messages.pricing_subheading') }}</h2>
            @endunless
        </div>

        <style>
            .vendor-toggle,
            .vendor-toggle-reseller {
                display: inline-flex;
                gap: .5rem;
                background: #eef2ff;
                border-radius: 12px;
                padding: .375rem;
            }

            .pricing-controls {
                gap: 1rem;
                flex-wrap: wrap;
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
               justify-content: start !important
            }

            #real-toggle label.form-switch span {
                white-space: normal;
                line-height: 1.3;
            }

            .vendor-toggle .tg,
            .vendor-toggle-reseller .tg {
                border: 0;
                background: transparent;
                padding: .5rem .9rem;
                border-radius: 10px;
                font-weight: 700;
                color: #1e293b;
                cursor: pointer;
                line-height: 1.2;
            }

            .vendor-toggle .tg.active,
            .vendor-toggle-reseller .tg.active {
                background: #2563eb;
                color: #fff;
                box-shadow: 0 6px 14px rgba(37, 99, 235, .25);
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

            #normalPackages,
            #resellerPackages .reseller-wrapper {
                display: grid;
                gap: 20px;
            }

            #normalPackages {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            #resellerPackages .reseller-wrapper {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            @media (max-width: 1200px) {

                #normalPackages,
                #resellerPackages .reseller-wrapper {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }
            }

            @media (max-width: 992px) {

                #normalPackages,
                #resellerPackages .reseller-wrapper {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 576px) {

                #normalPackages,
                #resellerPackages .reseller-wrapper {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-width: 768px) {

                #normalPackages,
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
        </style>

        <div class="pricing-controls d-flex align-items-center justify-content-between mb-3 mt-3">
            <div id="real-toggle">
                <label class="form-switch m-0">
                    <input type="checkbox" id="resellerToggle">
                    <i></i>
                    <span>{{ __('messages.show_reseller_packages') }}</span>
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
                    <span style="color:red;">1 {{ __('messages.credit') }}</span> = {{ __('messages.1_month') }}
                    &nbsp;<i class="fa fa-plus"></i>&nbsp;
                    <span style="color:red;">5 {{ __('messages.credit') }}</span> = {{ __('messages.6_months') }}
                    &nbsp;<i class="fa fa-plus"></i>&nbsp;
                    <span style="color:red;">10 {{ __('messages.credit') }}</span> = {{ __('messages.12_months') }}
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
                @endphp

                <div class="price-block scroll-item pkg-item" data-type="iptv" data-vendor="{{ $vendorKey }}">
                    <div class="inner-box custom-color">
                        <div class="upper-box"
                            @unless ($isMobile ?? false) style="background-image:url('{{ asset('images/background/pattern-4.webp') }}');" @endunless>
                            <ul class="icon-list">
                                <li><span class="icon"><img src="{{ asset('images/icons/service-1.svg') }}"
                                            alt="IPTV" width="48" height="48"></span></li>
                            </ul>
                            <h4>{{ $package['title'] ?? '' }}<span>{!! $package['price'] ?? '' !!}</span></h4>
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
                                        'plan' => $package['title'] ?? '',
                                        'vendor' => $vendorKey,
                                    ]) }}"
                                    class="theme-btn btn-style-four">
                                    <span class="txt">{{ __('messages.buy_now') }}</span>
                                </a>

                                @if ($buyPrice)
                                    <a  rel="noopener"
                                        href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_package', ['plan' => $package['title'] ?? '', 'price' => $buyPrice])) }}">
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
                        $vendorResKey = strtolower(data_get($plan, 'vendor', 'opplex'));

                        $plainPrice = trim(strip_tags($plan['price'] ?? ''));
                        preg_match_all('/\d+(?:\.\d+)?/', $plainPrice, $m);
                        $buyPrice = $m[0] ? end($m[0]) : null;
                    @endphp

                    <div class="price-block reseller-price-block pkg-item d-flex flex-column justify-content-between"
                        data-type="reseller" data-vendor="{{ $vendorResKey }}">
                        <div class="inner-box custom-color">
                            <div class="upper-box"
                                @unless ($isMobile ?? false)
                                    style="background-image:url('{{ asset('images/background/pattern-4.webp') }}');"
                                @endunless>
                                <ul class="icon-list">
                                    @foreach ($plan['icons'] ?? [] as $icon)
                                        <li><span class="icon"><img src="{{ asset($icon) }}" alt="Reseller Icon"
                                                    width="48" height="48"></span></li>
                                    @endforeach
                                </ul>
                                <h4>{{ $plan['title'] }}<span>{!! $plan['price'] !!}</span></h4>
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
                                            'plan' => $plan['title'],
                                            'vendor' => $vendorResKey,
                                        ]) }}"
                                        class="theme-btn btn-style-four">
                                        <span class="txt">{{ __('messages.buy_now') }}</span>
                                    </a>

                                    <a  rel="noopener"
                                        href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_package', ['plan' => $plan['title'], 'price' => $buyPrice])) }}">
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
            const vendor = getActiveVendor(iptvVendorToggle);
            iptvCards.forEach(card => {
                const cardVendor = norm(card.dataset.vendor);
                const show = cardVendor === vendor;
                card.style.setProperty('display', show ? 'block' : 'none', 'important');
            });
        }

        function renderReseller() {
            const showReseller = resellerToggle && resellerToggle.checked;
            const vendor = getActiveVendor(resellerVendorToggle);

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
                card.style.setProperty('display', showCard ? 'block' : 'none', 'important');
            });
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
                renderReseller();
            });
        }

        renderIptv();
        renderReseller();
    });
</script>
