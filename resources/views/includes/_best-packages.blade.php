@php
    $packages = [
        [
            'title' => 'Monthly',
            'price' => 'PKR 350 / monthly',
            'features' => ['No Buffer', '24/7 Customer Support', 'Regular Updates and Maintenance', 'Quality Content'],
        ],
        [
            'title' => 'Half Yearly',
            'price' => 'PKR 1799 / half yearly',
            'features' => ['No Buffer', '24/7 Customer Support', 'Regular Updates and Maintenance', 'Quality Content'],
        ],
        [
            'title' => 'Yearly',
            'price' => 'PKR 3400 / yearly',
            'features' => ['No Buffer', '24/7 Customer Support', 'Regular Updates and Maintenance', 'Quality Content'],
        ],
    ];

    $resellerPlans = [
        [
            'title' => 'Starter Reseller Package',
            'price' => 'PKR 4,399 / 20 Credits',
            'icons' => ['images/icons/service-1.svg'],
            'features' => ['99.9% Uptime', 'No Credit Expiry', 'Unlimited Trails', 'No Make Sub-Reseller'],
            'button_link' => 'buy-now-panel',
            'delay' => '0ms',
        ],
        [
            'title' => 'Essential Reseller Bundle',
            'price' => 'PKR 10,499 / 50 Credits',
            'icons' => ['images/icons/service-2.svg'],
            'features' => ['99.9% Uptime', 'No Credit Expiry', 'Unlimited Trails', 'No Make Sub-Reseller'],
            'button_link' => 'buy-now-panel',
            'delay' => '150ms',
        ],
        [
            'title' => 'Pro Reseller Suite',
            'price' => 'PKR 18,999 / 100 Credits',
            'icons' => ['images/icons/service-3.svg'],
            'features' => ['99.9% Uptime', 'No Credit Expiry', 'Unlimited Trails', 'No Make Sub-Reseller'],
            'button_link' => 'buy-now-panel',
            'delay' => '300ms',
        ],
        [
            'title' => 'Advanced Reseller Toolkit',
            'price' => 'PKR 35,999 / 200 Credits',
            'icons' => ['images/icons/service-1.svg', 'images/icons/service-2.svg', 'images/icons/service-3.svg'],
            'features' => ['99.9% Uptime', 'No Credit Expiry', 'Unlimited Trails', 'Make Sub-Reseller'],
            'button_link' => 'buy-now-panel',
            'delay' => '450ms',
        ],
    ];

@endphp

<section class="pricing-section style-two" id="pricing-section">
    <div class="auto-container">

        <div class="{{ $containerClass }}">
            @if (!$agent->isMobile())
                <div class="separator"></div>
            @endif
            <h2>Discover Our Best Packages</h2>
        </div>

        <div id="real-toggle" class="text-center mb-4 mt-4" style="display: none;">
            <label class="form-switch">
                <input type="checkbox" id="resellerToggle">
                <i></i>
                <span>Show Reseller Packages</span>
            </label>
        </div>

        <div id="creditInfo" class="sec-title centered mb-4" style="display: none;">
            <p><strong>
                    <span style="color: red;">1 Credit</span> = 1 Month&nbsp;&nbsp;
                    <i class="fa fa-plus"></i>&nbsp;&nbsp;
                    <span style="color: red;">5 Credit</span> = 6 Months&nbsp;&nbsp;
                    <i class="fa fa-plus"></i>&nbsp;&nbsp;
                    <span style="color: red;">10 Credit</span> = 12 Months
                </strong></p>
        </div>

        <div class="scroll-wrapper normal-wrapper" id="normalPackages">
            @foreach ($packages as $package)
                <div class="price-block scroll-item">
                    <div class="inner-box custom-color">
                        <div class="upper-box"
                            style="{{ !$agent->isMobile() ? 'background-image: url(images/background/pattern-4.webp);' : '' }}">
                            <ul class="icon-list">
                                <li><span class="icon"><img src="images/icons/service-1.svg" alt=""
                                            width="48" height="48" /></span></li>
                            </ul>
                            <h4>{{ $package['title'] }}<span>{{ $package['price'] }}</span></h4>
                        </div>
                        <div class="lower-box">
                            <ul class="price-list">
                                @foreach ($package['features'] as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>
                            <div class="button-box package-price-button d-flex align-items-center">
                                <a href="{{ route('buynow') }}" class="theme-btn btn-style-four">
                                    <span class="txt">Buy Now</span>
                                </a>
                                <a target="_blank" href="https://api.whatsapp.com/send?phone=923121108582">
                                    <img class="whatsapp" src="{{ asset('images/whatsapp.webp') }}" width="32px"
                                        height="32px" loading="lazy" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div id="resellerPackages" style="display: none;">
            <div class="scroll-wrapper-reseller reseller-wrapper" style="display: flex; overflow-x: auto; gap: 20px;">
                @foreach ($resellerPlans as $plan)
                    <div class="price-block reseller-price-block scroll-item-reseller" style="min-width: 300px; flex-shrink: 0;">
                        <div class="inner-box custom-color">
                            <div class="upper-box"
                                style="{{ !$agent->isMobile() ? 'background-image: url(images/background/pattern-4.webp);' : '' }}">
                                <ul class="icon-list">
                                    @foreach ($plan['icons'] as $icon)
                                        <li>
                                            <span class="icon">
                                                <img src="{{ asset($icon) }}" alt="Reseller Icon" width="48px"
                                                    height="48px" />
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                                <h4>{{ $plan['title'] }}<span>{{ $plan['price'] }}</span></h4>
                            </div>
                            <div class="lower-box">
                                <ul class="price-list">
                                    @foreach ($plan['features'] as $feature)
                                        <li>{{ $feature }}</li>
                                    @endforeach
                                </ul>
                                <div class="button-box">
                                    <a href="{{ url($plan['button_link']) }}" class="theme-btn btn-style-four"><span
                                            class="txt">Get started</span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>




    </div>
</section>
