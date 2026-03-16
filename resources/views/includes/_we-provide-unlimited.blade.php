<style>
    .unlimited-showcase {
        position: relative;
        padding: 92px 0;
        background: linear-gradient(180deg, #ffffff 0%, #f7faff 100%);
        overflow: hidden;
    }

    .unlimited-showcase__shell {
        position: relative;
    }

    .unlimited-showcase__grid {
        display: grid;
        grid-template-columns: minmax(0, 1.08fr) minmax(0, .92fr);
        gap: 42px;
        align-items: center;
    }

    .unlimited-showcase__media {
        position: relative;
        min-height: 610px;
        border-radius: 28px;
        background: linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
        overflow: hidden;
    }

    .unlimited-showcase__main {
        position: absolute;
        inset: 0 auto 0 0;
        width: min(54%, 430px);
        border-radius: 28px;
        overflow: hidden;
    }

    .unlimited-showcase__main img,
    .unlimited-showcase__screen img,
    .unlimited-showcase__person img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .unlimited-showcase__screen {
        position: absolute;
        top: 42px;
        right: 24px;
        width: min(42%, 320px);
        padding: 8px;
        border-radius: 24px;
        background: #ffffff;
        border: 1px solid #e8edf5;
        box-shadow: 0 18px 44px rgba(15, 23, 42, .12);
    }

    .unlimited-showcase__screen img {
        aspect-ratio: 1.28;
        border-radius: 18px;
    }

    .unlimited-showcase__person-wrap {
        position: absolute;
        right: 10px;
        bottom: 0;
        width: min(48%, 360px);
        display: flex;
        justify-content: center;
        align-items: flex-end;
    }

    .unlimited-showcase__person-wrap::before {
        content: "";
        position: absolute;
        left: 50%;
        bottom: 32px;
        width: 280px;
        height: 280px;
        transform: translateX(-50%);
        border-radius: 50%;
        background: linear-gradient(180deg, #ff2e18 0%, #e21103 100%);
    }

    .unlimited-showcase__person {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 320px;
    }

    .unlimited-showcase__person img {
        height: auto;
        object-fit: contain;
    }

    .unlimited-showcase__signal {
        position: absolute;
        left: 48%;
        bottom: 104px;
        width: 94px;
        height: 94px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #ffffff;
        border: 1px solid #e8edf5;
        box-shadow: 0 16px 36px rgba(15, 23, 42, .12);
    }

    .unlimited-showcase__signal img {
        width: 42px;
        height: 42px;
        object-fit: contain;
    }

    .unlimited-showcase__eyebrow {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 0 14px;
        margin-bottom: 18px;
        border-radius: 999px;
        background: #eef4ff;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .unlimited-showcase__content h2 {
        margin: 0 0 16px;
        color: #0f172a;
        font-size: clamp(32px, 4vw, 58px);
        line-height: 1.02;
        letter-spacing: -.04em;
    }

    .unlimited-showcase__intro {
        margin: 0 0 26px;
        max-width: 620px;
        color: #607089;
        font-size: 17px;
        line-height: 1.7;
    }

    .unlimited-showcase__list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        gap: 16px;
    }

    .unlimited-showcase__item {
        display: grid;
        grid-template-columns: 58px minmax(0, 1fr);
        gap: 18px;
        align-items: start;
        padding: 20px 22px;
        border-radius: 22px;
        background: #f9fbff;
        border: 1px solid #e8edf5;
    }

    .unlimited-showcase__icon {
        width: 58px;
        height: 58px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        background: #ffffff;
        border: 1px solid #e8edf5;
        color: #ff2e18;
        font-size: 18px;
        box-shadow: 0 12px 24px rgba(15, 23, 42, .06);
    }

    .unlimited-showcase__copy strong {
        display: block;
        margin-bottom: 8px;
        color: #0f172a;
        font-size: 28px;
        line-height: 1.14;
        letter-spacing: -.03em;
    }

    .unlimited-showcase__copy span {
        display: block;
        color: #607089;
        font-size: 16px;
        line-height: 1.75;
    }

    @media (max-width: 991px) {
        .unlimited-showcase {
            padding: 72px 0;
        }

        .unlimited-showcase__panel {
            padding: 18px;
            border-radius: 24px;
        }

        .unlimited-showcase__grid {
            grid-template-columns: 1fr;
            gap: 28px;
        }

        .unlimited-showcase__media {
            min-height: 520px;
        }

        .unlimited-showcase__content h2 {
            font-size: 38px;
        }

        .unlimited-showcase__copy strong {
            font-size: 24px;
        }
    }

    @media (max-width: 767px) {
        .unlimited-showcase {
            padding: 54px 0;
        }

        .unlimited-showcase__media {
            min-height: 410px;
        }

        .unlimited-showcase__main {
            width: 52%;
        }

        .unlimited-showcase__screen {
            top: 18px;
            right: 14px;
            width: 44%;
            border-radius: 18px;
        }

        .unlimited-showcase__person-wrap {
            width: 48%;
            right: 4px;
        }

        .unlimited-showcase__person-wrap::before {
            width: 190px;
            height: 190px;
            bottom: 24px;
        }

        .unlimited-showcase__signal {
            left: 42%;
            bottom: 86px;
            width: 72px;
            height: 72px;
        }

        .unlimited-showcase__signal img {
            width: 32px;
            height: 32px;
        }

        .unlimited-showcase__content h2 {
            font-size: 29px;
        }

        .unlimited-showcase__intro {
            font-size: 15px;
            line-height: 1.6;
        }

        .unlimited-showcase__item {
            grid-template-columns: 48px minmax(0, 1fr);
            gap: 14px;
            padding: 16px;
            border-radius: 18px;
        }

        .unlimited-showcase__icon {
            width: 48px;
            height: 48px;
            border-radius: 15px;
        }

        .unlimited-showcase__copy strong {
            font-size: 20px;
        }

        .unlimited-showcase__copy span {
            font-size: 14px;
            line-height: 1.65;
        }
    }
</style>

<section class="network-section unlimited-showcase @unless ($isMobile) @else p-0 @endunless {{ !empty($useSectionSkeletons) ? 'skeleton-section' : '' }}"
    @if (!empty($useSectionSkeletons)) data-skeleton-section @endif
    aria-label="Opplex IPTV Features Section">
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
                </div>
            </div>
        </div>
    @endif

    <div class="auto-container unlimited-showcase__shell">
        <div class="inner-container unlimited-showcase__panel">
            <div class="unlimited-showcase__grid">
                @unless ($isMobile)
                    <div class="unlimited-showcase__media" aria-hidden="true">
                        <div class="unlimited-showcase__main">
                            <img src="{{ asset('images/resource/network-4.webp') }}" alt="IPTV streaming setup image" />
                        </div>
                        <div class="unlimited-showcase__screen">
                            <img src="{{ asset('images/resource/network-5.webp') }}" alt="IPTV content preview screen" />
                        </div>
                        <div class="unlimited-showcase__person-wrap">
                            <div class="unlimited-showcase__person">
                                <img src="{{ asset('images/resource/network-3.webp') }}" alt="High-quality IPTV connection graphic" />
                            </div>
                        </div>
                        <div class="unlimited-showcase__signal">
                            <img src="{{ asset('images/icons/service-4.webp') }}" alt="" aria-hidden="true" />
                        </div>
                    </div>
                @endunless

                <div class="unlimited-showcase__content" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                    <div class="unlimited-showcase__eyebrow">{{ __('messages.home_unlimited_eyebrow') }}</div>
                    <h3 aria-label="IPTV Network Features Heading">{{ __('messages.network_heading') }}</h3>
                    @unless (request()->routeIs('home') || request()->is('/') || request()->routeIs('about') || request()->is('reseller-panel'))
                        <h3 class="h6" aria-label="IPTV Network Features Heading">{{ __('messages.subheadingiptv') }}</h3>
                    @endunless
                    <p class="unlimited-showcase__intro">{{ __('messages.home_unlimited_intro') }}</p>

                    <ul class="unlimited-showcase__list" aria-label="List of IPTV Features">
                        @foreach ($features as $feature)
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
