@php
    $packages = [
        [
            'title' => __('messages.monthly'),
            'price' => __('messages.monthly_price'),
            'features' => [
                __('messages.no_buffer'),
                __('messages.support_24_7'),
                __('messages.regular_updates'),
                __('messages.quality_content'),
            ],
        ],
        [
            'title' => __('messages.half_yearly'),
            'price' => __('messages.half_yearly_price'),
            'features' => [
                __('messages.no_buffer'),
                __('messages.support_24_7'),
                __('messages.regular_updates'),
                __('messages.quality_content'),
            ],
        ],
        [
            'title' => __('messages.yearly'),
            'price' => __('messages.yearly_price'),
            'features' => [
                __('messages.no_buffer'),
                __('messages.support_24_7'),
                __('messages.regular_updates'),
                __('messages.quality_content'),
            ],
        ],
    ];

    $resellerPlans = [
        [
            'title' => __('messages.starter_reseller'),
            'price' => __('messages.starter_price'),
            'icons' => ['images/icons/service-1.svg'],
            'features' => [
                __('messages.uptime'),
                __('messages.no_credit_expiry'),
                __('messages.unlimited_trials'),
                __('messages.no_subreseller'),
            ],
            'button_link' => 'buy-now-panel',
            'delay' => '0ms',
        ],
        [
            'title' => __('messages.essential_reseller'),
            'price' => __('messages.essential_price'),
            'icons' => ['images/icons/service-2.svg'],
            'features' => [
                __('messages.uptime'),
                __('messages.no_credit_expiry'),
                __('messages.unlimited_trials'),
                __('messages.no_subreseller'),
            ],
            'button_link' => 'buy-now-panel',
            'delay' => '150ms',
        ],
        [
            'title' => __('messages.pro_reseller'),
            'price' => __('messages.pro_price'),
            'icons' => ['images/icons/service-3.svg'],
            'features' => [
                __('messages.uptime'),
                __('messages.no_credit_expiry'),
                __('messages.unlimited_trials'),
                __('messages.no_subreseller'),
            ],
            'button_link' => 'buy-now-panel',
            'delay' => '300ms',
        ],
        [
            'title' => __('messages.advanced_reseller'),
            'price' => __('messages.advanced_price'),
            'icons' => ['images/icons/service-1.svg', 'images/icons/service-2.svg', 'images/icons/service-3.svg'],
            'features' => [
                __('messages.uptime'),
                __('messages.no_credit_expiry'),
                __('messages.unlimited_trials'),
                __('messages.yes_subreseller'),
            ],
            'button_link' => 'buy-now-panel',
            'delay' => '450ms',
        ],
    ];
@endphp

<section class="pricing-section style-two" id="pricing-section" aria-label="IPTV Pricing Plans and Reseller Packages">
    <div class="auto-container">

        <div class="{{ $containerClass }}">
            @if (!$agent->isMobile())
                <div class="separator"></div>
            @endif
            <h2 aria-label="Choose Your IPTV Plan">{{ __('messages.pricing_heading') }}</h2>
        </div>

        <div id="real-toggle" class="text-center mb-4 mt-4" style="display: none;"
            aria-label="Toggle to view reseller packages">
            <label class="form-switch">
                <input type="checkbox" id="resellerToggle" aria-label="Reseller Package Toggle">
                <i></i>
                <span>{{ __('messages.show_reseller_packages') }}</span>
            </label>
        </div>

        <div id="creditInfo" class="sec-title centered mb-4" style="display: none;"
            aria-label="IPTV Credit System Info">
            <p><strong>
                    <span style="color: red;">1 {{ __('messages.credit') }}</span> =
                    {{ __('messages.1_month') }}&nbsp;&nbsp;
                    <i class="fa fa-plus"></i>&nbsp;&nbsp;
                    <span style="color: red;">5 {{ __('messages.credit') }}</span> =
                    {{ __('messages.6_months') }}&nbsp;&nbsp;
                    <i class="fa fa-plus"></i>&nbsp;&nbsp;
                    <span style="color: red;">10 {{ __('messages.credit') }}</span> = {{ __('messages.12_months') }}
                </strong></p>
        </div>

        <div class="scroll-wrapper normal-wrapper" id="normalPackages" aria-label="Standard IPTV Packages List">
            @foreach ($packages as $package)
                <div class="price-block scroll-item" aria-label="{{ $package['title'] }} IPTV Package">
                    <div class="inner-box custom-color">
                        <div class="upper-box"
                            style="{{ !$agent->isMobile() ? 'background-image: url(' . asset('images/background/pattern-4.webp') . ');' : '' }}">
                            <ul class="icon-list" aria-label="IPTV Service Icons">
                                <li>
                                    <span class="icon">
                                        <img src="{{ asset('images/icons/service-1.svg') }}" alt="Live IPTV Icon"
                                            width="48" height="48" />
                                    </span>
                                </li>
                            </ul>
                            <h4>{{ $package['title'] }}<span aria-label="Price">{!! $package['price'] !!}</span></h4>
                        </div>
                        <div class="lower-box">
                            <ul class="price-list" aria-label="Package Features">
                                @foreach ($package['features'] as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>
                            <div class="button-box package-price-button d-flex align-items-center">
                                <a href="{{ route('buynow') }}" class="theme-btn btn-style-four"
                                    aria-label="Buy Now {{ $package['title'] }}">
                                    <span class="txt">{{ __('messages.buy_now') }}</span>
                                </a>

                                <a target="_blank"
                                    href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_package', ['plan' => $package['title'], 'price' => $package['price']])) }}"
                                    aria-label="Contact via WhatsApp">
                                    <img class="whatsapp" src="{{ asset('images/whatsapp.webp') }}" width="32"
                                        height="32" alt="WhatsApp Icon" loading="lazy" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div id="resellerPackages" style="display: none;" aria-label="Reseller IPTV Packages">
            <div class="scroll-wrapper-reseller reseller-wrapper" style="display: flex; overflow-x: auto; gap: 20px;">
                @foreach ($resellerPlans as $plan)
                    <div class="price-block reseller-price-block scroll-item-reseller d-flex flex-column justify-content-between"
                        style="min-width: 300px; flex-shrink: 0; height: 100%;"
                        aria-label="{{ $plan['title'] }} Reseller Plan">
                        <div class="inner-box custom-color">
                            <div class="upper-box"
                                style="{{ !$agent->isMobile() ? 'background-image: url(' . asset('images/background/pattern-4.webp') . ');' : '' }}">
                                <ul class="icon-list" aria-label="Reseller Plan Icons">
                                    @foreach ($plan['icons'] as $icon)
                                        <li>
                                            <span class="icon">
                                                <img src="{{ asset($icon) }}" alt="Reseller Package Icon"
                                                    width="48" height="48" />
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                                <h4>{{ $plan['title'] }}<span aria-label="Price">{!! $plan['price'] !!}</span></h4>
                            </div>
                            <div class="lower-box">
                                <ul class="price-list" aria-label="Reseller Plan Features">
                                    @foreach ($plan['features'] as $feature)
                                        <li>{{ $feature }}</li>
                                    @endforeach
                                </ul>
                                <div class="button-box button-box-2">
                                    <a href="{{route("buy-now-panel")}}"
                                        class="theme-btn btn-style-four">
                                        <span class="txt">{{ __('messages.buy_now') }}</span>
                                    </a>
                                    <a target="_blank"
                                        href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_package', ['plan' => $package['title'], 'price' => $package['price']])) }}"
                                        aria-label="Contact via WhatsApp">
                                        <img class="whatsapp" src="{{ asset('images/whatsapp.webp') }}" width="32"
                                            height="32" alt="WhatsApp Icon" loading="lazy" />
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
