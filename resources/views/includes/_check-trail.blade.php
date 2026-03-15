<style>
    .trial-cta {
        position: relative;
        overflow: hidden;
        background: transparent;
        isolation: isolate;
    }

    .trial-cta__shell {
        position: relative;
        z-index: 1;
        max-width: 1320px;
        margin: 0 auto;
        padding: 20px 24px 28px;
    }

    .trial-cta__panel {
        position: relative;
        border-radius: 28px;
        padding: 34px 40px;
        border: 1px solid #e6edf7;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        box-shadow: 0 24px 54px rgba(15, 23, 42, .08);
    }

    .trial-cta__grid {
        display: grid;
        grid-template-columns: minmax(0, 1.5fr) minmax(280px, .72fr);
        gap: 28px;
        align-items: center;
    }

    .trial-cta__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        background: #eaf2ff;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .14em;
        text-transform: uppercase;
        margin-bottom: 16px;
    }

    .trial-cta__title {
        margin: 0;
        font-size: clamp(34px, 4vw, 58px);
        line-height: .98;
        letter-spacing: -.04em;
        color: #0f172a;
        max-width: 760px;
    }

    .trial-cta__text {
        margin: 16px 0 0;
        max-width: 620px;
        font-size: 17px;
        line-height: 1.65;
        color: #475569;
    }

    .trial-cta__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 24px;
    }

    .trial-cta__chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #0f172a;
        font-size: 13px;
        font-weight: 600;
    }

    .trial-cta__chip strong {
        font-weight: 800;
    }

    .trial-cta__actions {
        display: flex;
        justify-content: flex-end;
    }

    .trial-cta__action-card {
        width: 100%;
        max-width: 380px;
        padding: 18px;
        border-radius: 24px;
        background: #f8fbff;
        border: 1px solid #dbe7f6;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .8);
    }

    .trial-cta__action-label {
        display: block;
        margin-bottom: 12px;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .trial-cta__button {
        width: 100%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border-radius: 18px;
        padding: 17px 22px;
        background: linear-gradient(135deg, #0b1434 0%, #15265d 100%);
        color: #ffffff !important;
        font-size: 18px;
        font-weight: 800;
        letter-spacing: -.01em;
        box-shadow: 0 18px 32px rgba(4, 11, 35, .24);
        transition: transform .22s ease, box-shadow .22s ease, background .22s ease;
    }

    .trial-cta__button:hover {
        transform: translateY(-2px);
        box-shadow: 0 24px 38px rgba(4, 11, 35, .3);
        color: #ffffff !important;
    }

    .trial-cta__button .lnr {
        font-size: 16px;
    }

    @media (max-width: 991px) {
        .trial-cta__shell {
            padding: 14px 14px 22px;
        }

        .trial-cta__panel {
            padding: 24px 20px;
            border-radius: 22px;
        }

        .trial-cta__grid {
            grid-template-columns: 1fr;
        }

        .trial-cta__actions {
            justify-content: stretch;
        }

        .trial-cta__action-card {
            max-width: none;
        }

        .trial-cta__text {
            font-size: 15px;
        }
    }
</style>

<section class="trial-cta {{ !empty($useSectionSkeletons) ? 'skeleton-section skeleton-section--cta' : '' }}"
    @if (!empty($useSectionSkeletons)) data-skeleton-section @endif
    dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
    aria-label="Start Your IPTV Free Trial with Opplex"
    role="region">
    @if (!empty($useSectionSkeletons))
        <div class="section-skeleton__overlay" aria-hidden="true">
            <div class="section-skeleton__content">
                <div class="section-skeleton__meta">
                    <span class="section-skeleton__pill"></span>
                    <span class="section-skeleton__line section-skeleton__line--lg"></span>
                    <span class="section-skeleton__line section-skeleton__line--md"></span>
                </div>
                <span class="section-skeleton__button"></span>
            </div>
        </div>
    @endif

    <div class="trial-cta__shell">
        <div class="trial-cta__panel">
            <div class="trial-cta__grid {{ $isRtl ? 'rtl-row' : '' }}">
                <div class="trial-cta__content" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                    <span class="trial-cta__eyebrow">Instant Access</span>
                    <h3 class="trial-cta__title">{{ __('messages.trial_title') }}</h3>
                    <p class="trial-cta__text">
                        Stream in 4K, test channel stability, and experience premium IPTV performance before you commit.
                    </p>
                    <div class="trial-cta__meta">
                        <span class="trial-cta__chip"><strong>4K</strong> streaming</span>
                        <span class="trial-cta__chip"><strong>12K+</strong> channels</span>
                        <span class="trial-cta__chip"><strong>Fast</strong> activation</span>
                    </div>
                </div>

                <div class="trial-cta__actions" style="text-align: {{ $isRtl ? 'right' : 'left' }};">
                    <div class="trial-cta__action-card">
                        <span class="trial-cta__action-label">WhatsApp Trial</span>
                        <a href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_trial')) }}"
                            target="_blank"
                            rel="noopener"
                            class="trial-cta__button"
                            data-trial
                            data-wa-href="https://wa.me/16393903194?text={{ urlencode(__('messages.whatsapp_trial')) }}"
                            aria-label="Start your IPTV free trial now">
                            <span>{{ __('messages.trial_button') }}</span>
                            <i class="lnr lnr-arrow-right {{ $isRtl ? 'rtl-rotate' : '' }}" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
