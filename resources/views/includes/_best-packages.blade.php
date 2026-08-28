<section class="pricing-section style-two" id="pricing-section" aria-label="{{ __('document_ui.home.pricing_aria') }}">
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

        <div class="{{ $containerClass ?? 'container' }}">
            @unless ($isMobile ?? false)
                <div class="separator"></div>
            @endunless

            <h3 class="h3"><b>{{ $isDocumentEnglishPricing ? $documentPricing['heading'] : ($pricingSection['heading'] ?? __('messages.pricing_heading')) }}</b></h3>

            @unless (request()->is('packages') || request()->is('pricing') || request()->is('reseller-panel'))
                <p class="h4">{{ $isDocumentEnglishPricing ? ($documentPricing['subheading'] ?? ($pricingSection['subheading'] ?? __('messages.pricing_subheading'))) : ($pricingSection['subheading'] ?? __('messages.pricing_subheading')) }}</p>
            @endunless

            @if ($isDocumentEnglishPricing)
                <p class="home-document-pricing__intro">{{ $documentPricing['intro'] }}</p>
            @endif

            <aside class="alert alert-warning d-flex align-items-center mx-auto mt-3 mb-1 py-2 px-3 {{ ($isRtl ?? false) ? 'text-right' : 'text-left' }}"
                style="max-width: 760px;" dir="{{ ($isRtl ?? false) ? 'rtl' : 'ltr' }}"
                aria-label="{{ __('messages.final_sale_no_refunds') }}">
                <span class="fa fa-exclamation-circle {{ ($isRtl ?? false) ? 'ml-2' : 'mr-2' }}" aria-hidden="true"></span>
                <p class="mb-0">
                    <strong>{{ __('messages.final_sale_no_refunds') }}</strong>
                    {{ __('messages.final_sale_confirmed') }}
                    {{ __('messages.final_sale_verify_before_payment') }}
                </p>
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

                    $plainPrice = trim(strip_tags(data_get($package, 'price', '')));
                    preg_match_all('/(?:USD\s*)?\$\s*(\d+(?:\.\d+)?)/i', $plainPrice, $priceMatches);
                    $buyPrice = $priceMatches[1] ? end($priceMatches[1]) : null;

                    // Remove text in parentheses + embedded price from title.
                    $rawTitle = (string) data_get($package, 'title', '');
                    $titleNoParen = (string) preg_replace('/\s*\([^)]*\)/', '', $rawTitle);
                    $titleBase = trim((string) preg_replace('/\s*-\s*\$?\d+(?:\.\d+)?/i', '', $titleNoParen, 1));
                    $displayTitle = $titleBase;
                    $displayPrice = $package['price'] ?? '';
                    $displayFeatures = $package['features'] ?? [];

                    if ($isDocumentEnglishPricing) {
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
                    @if ($vendorKey !== 'opplex') style="display:none!important" @endif>
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
                                    ]) }}"
                                    class="theme-btn btn-style-four">
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

                        $plainPrice = trim(strip_tags($plan['price'] ?? ''));
                        preg_match_all('/(?:USD\s*)?\$\s*(\d+(?:\.\d+)?)/i', $plainPrice, $priceMatches);
                        $buyPrice = $priceMatches[1] ? end($priceMatches[1]) : null;
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
                                        ]) }}"
                                        class="theme-btn btn-style-four">
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

        window.addEventListener('resize', function() {
            renderIptv();
            renderReseller();
        });

        renderIptv();
        renderReseller();
    });
</script>
