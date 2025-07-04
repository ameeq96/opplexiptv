@php
    $resellerPlans = [
        [
            'title' => __('messages.reseller_starter'),
            'price' => 'PKR 4,399 / 20 Credits',
            'icons' => ['images/icons/service-1.svg'],
            'features' => [
                __('messages.uptime'),
                __('messages.no_expiry'),
                __('messages.unlimited_trials'),
                __('messages.no_subreseller'),
            ],
            'button_link' => 'buy-now-panel',
            'delay' => '0ms',
        ],
        [
            'title' => __('messages.reseller_essential'),
            'price' => 'PKR 10,499 / 50 Credits',
            'icons' => ['images/icons/service-2.svg'],
            'features' => [
                __('messages.uptime'),
                __('messages.no_expiry'),
                __('messages.unlimited_trials'),
                __('messages.no_subreseller'),
            ],
            'button_link' => 'buy-now-panel',
            'delay' => '150ms',
        ],
        [
            'title' => __('messages.reseller_pro'),
            'price' => 'PKR 18,999 / 100 Credits',
            'icons' => ['images/icons/service-3.svg'],
            'features' => [
                __('messages.uptime'),
                __('messages.no_expiry'),
                __('messages.unlimited_trials'),
                __('messages.no_subreseller'),
            ],
            'button_link' => 'buy-now-panel',
            'delay' => '300ms',
        ],
        [
            'title' => __('messages.reseller_advanced'),
            'price' => 'PKR 35,999 / 200 Credits',
            'icons' => ['images/icons/service-1.svg', 'images/icons/service-2.svg', 'images/icons/service-3.svg'],
            'features' => [
                __('messages.uptime'),
                __('messages.no_expiry'),
                __('messages.unlimited_trials'),
                __('messages.make_subreseller'),
            ],
            'button_link' => 'buy-now-panel',
            'delay' => '450ms',
        ],
    ];
@endphp

<section class="pricing-section style-two">
    <div class="auto-container">
        <div class="sec-title centered">
            <div class="title">{{ __('messages.pricing_plan_title') }}</div>
            <h2>{{ __('messages.choose_reseller_plan') }}</h2>
            <p><strong>
                    <span style="color: red;">1 {{ __('messages.credit') }}</span> = 1
                    {{ __('messages.month') }}&nbsp;&nbsp;
                    <i class="fa fa-plus"></i>&nbsp;&nbsp;
                    <span style="color: red;">5 {{ __('messages.credit') }}</span> = 6
                    {{ __('messages.months') }}&nbsp;&nbsp;
                    <i class="fa fa-plus"></i>&nbsp;&nbsp;
                    <span style="color: red;">10 {{ __('messages.credit') }}</span> = 12 {{ __('messages.months') }}
                </strong></p>
        </div>

        <div class="row clearfix">
            @foreach ($resellerPlans as $plan)
                <div class="price-block col-xl-3 col-lg-4 col-md-6 col-sm-12">
                    <div class="inner-box wow fadeInLeft" data-wow-delay="{{ $plan['delay'] }}"
                        data-wow-duration="1500ms">
                        <div class="upper-box"
                            style="background-image: url('{{ asset('images/background/pattern-4.webp') }}')">
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
                                <a href="{{ url($plan['button_link']) }}" class="theme-btn btn-style-four">
                                    <span class="txt">{{ __('messages.get_started') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
