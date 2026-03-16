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

