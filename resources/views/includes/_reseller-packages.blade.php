@php
    $resellerPlans = [
        [
            'title' => 'Starter Reseller Package',
            'price' => 'PKR 4,399 / 20 Credits',
            'icons' => ['images/icons/service-1.svg'],
            'features' => [
                '99.9% Uptime',
                'No Credit Expiry',
                'Unlimited Trails',
                'No Make Sub-Reseller'
            ],
            'button_link' => 'buy-now-panel',
            'delay' => '0ms',
        ],
        [
            'title' => 'Essential Reseller Bundle',
            'price' => 'PKR 10,499 / 50 Credits',
            'icons' => ['images/icons/service-2.svg'],
            'features' => [
                '99.9% Uptime',
                'No Credit Expiry',
                'Unlimited Trails',
                'No Make Sub-Reseller'
            ],
            'button_link' => 'buy-now-panel',
            'delay' => '150ms',
        ],
        [
            'title' => 'Pro Reseller Suite',
            'price' => 'PKR 18,999 / 100 Credits',
            'icons' => ['images/icons/service-3.svg'],
            'features' => [
                '99.9% Uptime',
                'No Credit Expiry',
                'Unlimited Trails',
                'No Make Sub-Reseller'
            ],
            'button_link' => 'buy-now-panel',
            'delay' => '300ms',
        ],
        [
            'title' => 'Advanced Reseller Toolkit',
            'price' => 'PKR 35,999 / 200 Credits',
            'icons' => [
                'images/icons/service-1.svg',
                'images/icons/service-2.svg',
                'images/icons/service-3.svg',
            ],
            'features' => [
                '99.9% Uptime',
                'No Credit Expiry',
                'Unlimited Trails',
                'Make Sub-Reseller'
            ],
            'button_link' => 'buy-now-panel',
            'delay' => '450ms',
        ],
    ];
@endphp

<section class="pricing-section style-two">
    <div class="auto-container">
        <!-- Sec Title -->
        <div class="sec-title centered">
            <div class="title">Pricing Plan</div>
            <h2>Choose your Reseller Credits plan</h2>
            <p><strong> 
                <span style="color: red;">1 Credit</span> = 1 Month&nbsp;&nbsp;
                <i class="fa fa-plus"></i>&nbsp;&nbsp;
                <span style="color: red;">5 Credit</span> = 6 Months&nbsp;&nbsp;
                <i class="fa fa-plus"></i>&nbsp;&nbsp;
                <span style="color: red;">10 Credit</span> = 12 Months
            </strong></p>
        </div>

        <div class="row clearfix">
            @foreach($resellerPlans as $plan)
                <div class="price-block col-xl-3 col-lg-4 col-md-6 col-sm-12">
                    <div class="inner-box wow fadeInLeft" data-wow-delay="{{ $plan['delay'] }}" data-wow-duration="1500ms">
                        <div class="upper-box" style="background-image: url(images/background/pattern-4.webp)">
                            <ul class="icon-list">
                                @foreach($plan['icons'] as $icon)
                                    <li>
                                        <span class="icon">
                                            <img src="{{ asset($icon) }}" alt="Reseller Icon" width="48px" height="48px" />
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                            <h4>{{ $plan['title'] }}<span>{{ $plan['price'] }}</span></h4>
                        </div>
                        <div class="lower-box">
                            <ul class="price-list">
                                @foreach($plan['features'] as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>
                            <div class="button-box">
                                <a href="{{ url($plan['button_link']) }}" class="theme-btn btn-style-four"><span class="txt">Get started</span></a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
