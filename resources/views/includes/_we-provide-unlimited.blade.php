
@once
    @push('styles')
        <style>
            .unlimited-showcase,
            .unlimited-showcase.p-0 {
                position: relative;
                overflow: hidden;
                padding: clamp(72px, 7vw, 104px) 0 !important;
                background: none;
            }

            .unlimited-showcase *,
            .unlimited-showcase *::before,
            .unlimited-showcase *::after {
                box-sizing: border-box;
            }

            .unlimited-showcase__panel {
                position: relative;
                overflow: visible;
                padding: 0;
                border: 0;
                border-radius: 0;
                background: none;
                box-shadow: none;
            }

            .unlimited-showcase__panel::before {
                display: none;
                position: absolute;
                inset: 0;
                background-image:
                    linear-gradient(rgba(255, 255, 255, .035) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255, 255, 255, .035) 1px, transparent 1px);
                background-size: 42px 42px;
                content: "";
                -webkit-mask-image: linear-gradient(135deg, #000, transparent 68%);
                mask-image: linear-gradient(135deg, #000, transparent 68%);
                pointer-events: none;
            }

            .unlimited-showcase__grid {
                position: relative;
                display: grid;
                grid-template-columns: minmax(380px, .9fr) minmax(0, 1.1fr);
                align-items: center;
                gap: clamp(34px, 4vw, 58px);
            }

            .unlimited-showcase .unlimited-showcase__media {
                display: grid;
                place-items: center;
                width: 100%;
                max-width: 560px;
                min-height: 0;
                aspect-ratio: 4 / 3;
                padding: clamp(18px, 3vw, 32px);
                margin-inline: auto;
                border: 1px solid rgba(15, 23, 42, .1);
                border-radius: 28px;
                background: linear-gradient(180deg, #f8fbff, #e9f0fa);
                box-shadow: 0 24px 54px rgba(15, 23, 42, .14);
            }

            .unlimited-showcase .unlimited-showcase__main {
                position: relative;
                inset: auto;
                width: 100%;
                max-width: 480px;
                overflow: hidden;
                border-radius: 22px;
                box-shadow: none;
            }

            .unlimited-showcase__screen {
                top: 26px;
                right: 20px;
                padding: 6px;
                border-color: #fff;
                box-shadow: 0 18px 36px rgba(15, 23, 42, .16);
            }

            .unlimited-showcase__person-wrap::before {
                box-shadow: 0 22px 42px rgba(226, 17, 3, .2);
            }

            .unlimited-showcase__signal {
                width: 78px;
                height: 78px;
                border: 5px solid rgba(255, 255, 255, .78);
                box-shadow: 0 16px 32px rgba(15, 23, 42, .16);
            }

            .unlimited-showcase__signal img {
                width: 34px;
                height: 34px;
            }

            .unlimited-showcase__content {
                min-width: 0;
                padding-block: 8px;
            }

            .unlimited-showcase__eyebrow {
                gap: 9px;
                min-height: 34px;
                margin-bottom: 18px;
                padding: 7px 13px;
                border: 1px solid rgba(220, 17, 27, .16);
                background: #fff1f2;
                color: #b80a14;
                font-size: 11px;
                font-weight: 800;
            }

            .unlimited-showcase__eyebrow::before {
                width: 7px;
                height: 7px;
                flex: 0 0 7px;
                border-radius: 50%;
                background: #ff4b36;
                box-shadow: 0 0 0 4px rgba(255, 75, 54, .14);
                content: "";
            }

            .unlimited-showcase .unlimited-showcase__content h3:not(.h6) {
                max-width: 760px;
                margin: 0 0 18px;
                color: #0f172a;
                font-size: clamp(38px, 3.5vw, 56px);
                line-height: 1.03;
                letter-spacing: -.045em;
                text-wrap: balance;
            }

            .unlimited-showcase__content .h6 {
                color: #334155;
            }

            .unlimited-showcase__intro {
                max-width: 720px;
                margin-bottom: 25px;
                color: #607089;
                font-size: 16px;
                line-height: 1.68;
            }

            .unlimited-showcase__list {
                gap: 13px;
            }

            .unlimited-showcase__item {
                position: relative;
                min-width: 0;
                grid-template-columns: 46px minmax(0, 1fr);
                gap: 15px;
                padding: 16px 18px;
                overflow: hidden;
                border-color: rgba(15, 23, 42, .1);
                border-radius: 18px;
                background: #fff;
                box-shadow: 0 14px 34px rgba(15, 23, 42, .07);
                transition: transform .2s ease, border-color .2s ease, background-color .2s ease;
            }

            .unlimited-showcase__item::before {
                position: absolute;
                inset-block: 15px;
                inset-inline-start: 0;
                width: 3px;
                border-radius: 999px;
                background: linear-gradient(180deg, #ff4b36, #dc111b);
                content: "";
            }

            .unlimited-showcase__item:hover {
                border-color: rgba(220, 17, 27, .24);
                background: #fff;
                box-shadow: 0 20px 42px rgba(15, 23, 42, .11);
                transform: translateY(-3px);
            }

            .unlimited-showcase__icon {
                width: 46px;
                height: 46px;
                border-color: rgba(255, 255, 255, .2);
                border-radius: 14px;
                background: linear-gradient(145deg, #ff3b27, #d90d19);
                box-shadow: 0 11px 22px rgba(220, 17, 27, .24);
                color: #fff;
                font-size: 15px;
            }

            .unlimited-showcase__copy {
                min-width: 0;
            }

            .unlimited-showcase__copy strong {
                margin-bottom: 6px;
                color: #0f172a;
                font-size: 19px;
                line-height: 1.25;
                letter-spacing: -.02em;
                overflow-wrap: anywhere;
            }

            .unlimited-showcase__copy span {
                color: #607089;
                font-size: 14px;
                line-height: 1.62;
                overflow-wrap: anywhere;
            }

            .unlimited-showcase__content[dir="rtl"] .unlimited-showcase__eyebrow,
            .unlimited-showcase__content[dir="rtl"] h3,
            .unlimited-showcase__content[dir="rtl"] strong {
                letter-spacing: 0;
            }

            @media (min-width: 1280px) {
                .unlimited-showcase__list {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    gap: 14px;
                }

                .unlimited-showcase__item {
                    min-height: 184px;
                    grid-template-columns: 42px minmax(0, 1fr);
                    gap: 12px;
                    padding: 17px 15px;
                }

                .unlimited-showcase__icon {
                    width: 42px;
                    height: 42px;
                }

                .unlimited-showcase__copy strong {
                    font-size: 18px;
                }

                .unlimited-showcase__copy span {
                    font-size: 13.5px;
                }
            }

            @media (max-width: 1180px) {
                .unlimited-showcase__panel {
                    padding: 0;
                    border-radius: 0;
                }

                .unlimited-showcase__grid {
                    grid-template-columns: minmax(0, 1fr);
                    gap: 36px;
                }

                .unlimited-showcase__media,
                .unlimited-showcase__content {
                    width: min(100%, 900px);
                    justify-self: center;
                }

                .unlimited-showcase__media {
                    min-height: 0;
                }

                .unlimited-showcase__list {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .unlimited-showcase__item {
                    min-height: 172px;
                }
            }

            @media (max-width: 767px) {
                .unlimited-showcase,
                .unlimited-showcase.p-0 {
                    padding: 56px 0 !important;
                }

                .unlimited-showcase__panel {
                    padding: 0;
                    border-radius: 0;
                }

                .unlimited-showcase__media {
                    min-height: 0;
                    border-radius: 22px;
                }

                .unlimited-showcase .unlimited-showcase__content h3:not(.h6) {
                    font-size: clamp(31px, 8vw, 42px);
                    line-height: 1.06;
                }

                .unlimited-showcase__intro {
                    font-size: 15px;
                }

                .unlimited-showcase__list {
                    grid-template-columns: minmax(0, 1fr);
                }

                .unlimited-showcase__item {
                    min-height: 0;
                }
            }

            @media (max-width: 520px) {
                .unlimited-showcase,
                .unlimited-showcase.p-0 {
                    padding: 48px 0 !important;
                }

                .unlimited-showcase__panel {
                    padding: 0;
                    border-radius: 0;
                }

                .unlimited-showcase__media {
                    min-height: 0;
                    border-radius: 18px;
                }

                .unlimited-showcase__main {
                    inset: auto;
                    border-radius: 15px;
                }

                .unlimited-showcase__screen {
                    top: 14px;
                    right: 11px;
                    padding: 4px;
                    border-radius: 14px;
                }

                .unlimited-showcase__person-wrap::before {
                    width: 180px;
                    height: 180px;
                }

                .unlimited-showcase__signal {
                    bottom: 66px;
                    width: 56px;
                    height: 56px;
                    border-width: 4px;
                }

                .unlimited-showcase__signal img {
                    width: 25px;
                    height: 25px;
                }

                .unlimited-showcase__eyebrow {
                    min-height: 30px;
                    margin-bottom: 14px;
                    padding: 6px 10px;
                    font-size: 10px;
                }

                .unlimited-showcase .unlimited-showcase__content h3:not(.h6) {
                    font-size: 29px;
                }

                .unlimited-showcase__item {
                    grid-template-columns: 42px minmax(0, 1fr);
                    gap: 12px;
                    padding: 14px;
                    border-radius: 16px;
                }

                .unlimited-showcase__icon {
                    width: 42px;
                    height: 42px;
                }

                .unlimited-showcase__copy strong {
                    font-size: 17px;
                }

                .unlimited-showcase__copy span {
                    font-size: 13.5px;
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .unlimited-showcase__item {
                    transition-duration: .01ms;
                }
            }
        </style>
    @endpush
@endonce

<section class="network-section unlimited-showcase @unless ($isMobile) @else p-0 @endunless"
    aria-label="{{ __('document_ui.home.features_aria') }}">

    @php
        $isDocumentEnglishUnlimited = request()->routeIs('home');
        $documentUnlimited = $isDocumentEnglishUnlimited ? __('document_home.unlimited') : [];
        $displayFeatures = $isDocumentEnglishUnlimited ? $documentUnlimited['features'] : $features;
    @endphp

    <div class="auto-container unlimited-showcase__shell">
        <div class="inner-container unlimited-showcase__panel">
            <div class="unlimited-showcase__grid">
                @unless ($isMobile)
                    <div class="unlimited-showcase__media" aria-hidden="true">
                        <div class="unlimited-showcase__main">
                            <svg viewBox="0 0 640 440" width="640" height="440" aria-hidden="true" focusable="false"
                                style="display: block; width: 100%; height: auto;">
                                <rect x="52" y="54" width="536" height="298" rx="24" fill="#132e44" />
                                <rect x="72" y="74" width="496" height="258" rx="12" fill="#1e5162" />
                                <path d="M290 150 390 203 290 256Z" fill="#86d8cd" />
                                <path d="M320 352V396M236 396H404" fill="none" stroke="#132e44" stroke-width="16" stroke-linecap="round" />
                            </svg>
                        </div>
                    </div>
                @endunless

                <div class="unlimited-showcase__content" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                    <div class="unlimited-showcase__eyebrow">{{ $isDocumentEnglishUnlimited ? $documentUnlimited['eyebrow'] : __('messages.home_unlimited_eyebrow') }}</div>
                    <h3 aria-label="{{ __('document_ui.home.network_heading_aria') }}">{{ $isDocumentEnglishUnlimited ? $documentUnlimited['heading'] : __('messages.network_heading') }}</h3>
                    @unless (request()->routeIs('home') || request()->is('/') || request()->routeIs('about') || request()->is('reseller-panel', 'reseller-plans'))
                        <h3 class="h6" aria-label="{{ __('document_ui.home.network_heading_aria') }}">{{ __('messages.subheadingiptv') }}</h3>
                    @endunless
                    <p class="unlimited-showcase__intro">{{ $isDocumentEnglishUnlimited ? $documentUnlimited['intro'] : __('messages.home_unlimited_intro') }}</p>

                    <ul class="unlimited-showcase__list" aria-label="{{ __('document_ui.home.features_list_aria') }}">
                        @foreach ($displayFeatures as $feature)
                            <li class="unlimited-showcase__item" aria-label="{{ $feature['title'] }}" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                                <span class="unlimited-showcase__icon flaticon-tick-1" aria-hidden="true"></span>
                                <div class="unlimited-showcase__copy">
                                    <strong>{{ $feature['title'] }}</strong>
                                    <span>{{ $feature['description'] }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
